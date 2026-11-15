<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Enums\PaymentType;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Log;
use App\Models\Payment;
use App\Models\PaymentStatus;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use LogicException;
use RuntimeException;

class BalanceService
{
    public const STATUS_SENT = 'Sent';

    public const STATUS_PARTIAL = 'Partially Paid';

    public const STATUS_PAID = 'Paid';

    public const STATUS_OVERDUE = 'Overdue';

    /**
     * Invoice statuses that can receive a payment.
     *
     * @var array<int, string>
     */
    public const PAYABLE_STATUSES = [
        self::STATUS_SENT,
        self::STATUS_PARTIAL,
        self::STATUS_OVERDUE,
    ];

    /**
     * Inject the audit log service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Normalise a money value to a two-decimal string.
     */
    public function money(mixed $value): string
    {
        return bcadd(trim((string) ($value ?? '0')), '0', 2);
    }

    /**
     * Convert an integer amount in pence to a two-decimal pounds string.
     */
    public function poundsFromPence(mixed $pence): string
    {
        return bcdiv((string) (int) $pence, '100', 2);
    }

    /**
     * Net amount received for an invoice: completed payments minus
     * completed refunds.
     */
    public function netPaid(Invoice $invoice): string
    {
        return bcsub(
            $this->sum($invoice->id, PaymentType::Payment),
            $this->sum($invoice->id, PaymentType::Refund),
            2
        );
    }

    /**
     * Outstanding balance, recomputed from the payments table.
     */
    public function balanceDue(Invoice $invoice): string
    {
        return bcsub($this->poundsFromPence($invoice->total), $this->netPaid($invoice), 2);
    }

    /**
     * Amount of a completed payment that has not yet been refunded.
     */
    public function refundableAmount(Payment $payment): string
    {
        if (! $payment->isPayment() || ! $payment->isCompleted()) {
            return '0.00';
        }

        $refunded = $this->money(
            Payment::query()
                ->where('parent_payment_id', $payment->id)
                ->where('type', PaymentType::Refund->value)
                ->where('status_id', PaymentStatus::idForKey(PaymentStatus::KEY_COMPLETED))
                ->toBase()
                ->selectRaw('COALESCE(SUM(amount), 0) AS total')
                ->value('total')
        );

        return bcsub($this->money($payment->amount), $refunded, 2);
    }

    /**
     * Recalculate an invoice's paid amount, paid date and status from its
     * completed payments.
     *
     * The caller MUST hold a transaction and a lockForUpdate() on the
     * invoice row. The status only moves between Sent, Partially Paid,
     * Paid and Overdue. An invoice in any other status (Draft, Cancelled)
     * keeps its status and only has its amounts refreshed.
     *
     * @throws LogicException
     */
    public function recalculate(Invoice $invoice, User $actor): Invoice
    {
        if (DB::transactionLevel() < 1) {
            throw new LogicException(
                'Recalculate an invoice balance inside a transaction that holds the invoice lock.'
            );
        }

        $invoice->loadMissing('status');

        $before = $this->auditLogService->snapshot($invoice);

        $net = $this->netPaid($invoice);
        $total = $this->poundsFromPence($invoice->total);
        $fullyPaid = bccomp($total, '0', 2) === 1 && bccomp($net, $total, 2) >= 0;

        $attributes = [
            'amount_paid' => (int) bcmul($net, '100', 0),
            'paid_at' => $fullyPaid ? ($invoice->paid_at ?? now()) : null,
            'updated_by' => $actor->id,
        ];

        $title = $invoice->status?->title;

        if (in_array($title, [...self::PAYABLE_STATUSES, self::STATUS_PAID], true)) {
            $attributes['status_id'] = $this->invoiceStatusId(
                $this->targetStatus((string) $title, $net, $fullyPaid)
            );
        }

        $invoice->forceFill($attributes)->save();
        $invoice->unsetRelation('status');

        $this->auditLogService->record(
            Log::ACTION_RECALCULATE_INVOICE_BALANCE,
            $actor,
            $invoice,
            [
                'before' => $before,
                'after' => $this->auditLogService->snapshot($invoice),
            ],
        );

        return $invoice;
    }

    /**
     * Choose the invoice status title that matches the net paid amount.
     */
    protected function targetStatus(string $current, string $net, bool $fullyPaid): string
    {
        if ($fullyPaid) {
            return self::STATUS_PAID;
        }

        if (bccomp($net, '0', 2) === 1) {
            return self::STATUS_PARTIAL;
        }

        // Nothing is held. An overdue invoice stays overdue, anything else
        // (including a fully refunded invoice) goes back to Sent.
        return $current === self::STATUS_OVERDUE ? self::STATUS_OVERDUE : self::STATUS_SENT;
    }

    /**
     * Sum completed, non-deleted rows of one type as a two-decimal string.
     *
     * The sum runs in SQL on the exact decimal column. toBase() applies the
     * soft delete and organisation scopes first.
     */
    protected function sum(int $invoiceId, PaymentType $type): string
    {
        return $this->money(
            Payment::query()
                ->where('invoice_id', $invoiceId)
                ->where('type', $type->value)
                ->where('status_id', PaymentStatus::idForKey(PaymentStatus::KEY_COMPLETED))
                ->toBase()
                ->selectRaw('COALESCE(SUM(amount), 0) AS total')
                ->value('total')
        );
    }

    /**
     * Resolve an invoice status ID from its title.
     *
     * @throws RuntimeException
     */
    protected function invoiceStatusId(string $title): int
    {
        $id = InvoiceStatus::query()->where('title', $title)->value('id');

        if ($id === null) {
            throw new RuntimeException("Invoice status '{$title}' is missing. Run InvoiceStatusSeeder.");
        }

        return (int) $id;
    }
}

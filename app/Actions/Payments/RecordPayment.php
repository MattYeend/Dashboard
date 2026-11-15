<?php

declare(strict_types=1);

namespace App\Actions\Payments;

use App\Enums\PaymentType;
use App\Models\Invoice;
use App\Models\Log;
use App\Models\Payment;
use App\Models\PaymentStatus;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Invoices\BalanceService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordPayment
{
    /**
     * Inject the required services.
     */
    public function __construct(
        protected readonly BalanceService $balance,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Record a completed payment, enforcing the outstanding balance under
     * a row lock on the invoice.
     *
     * @param  array<string, mixed>  $data  Prepared data from DataPreparationService
     *
     * @throws ValidationException
     */
    public function handle(array $data, User $actor): Payment
    {
        return DB::transaction(function () use ($data, $actor): Payment {
            $invoice = Invoice::query()
                ->with('status')
                ->whereKey($data['invoice_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($invoice->status?->title, BalanceService::PAYABLE_STATUSES, true)) {
                throw ValidationException::withMessages([
                    'invoice_id' => 'This invoice cannot receive payments in its current status.',
                ]);
            }

            $amount = $this->balance->money($data['amount']);

            if (bccomp($amount, '0', 2) !== 1) {
                throw ValidationException::withMessages([
                    'amount' => 'The amount must be greater than zero.',
                ]);
            }

            $due = $this->balance->balanceDue($invoice);

            if (bccomp($amount, $due, 2) === 1) {
                throw ValidationException::withMessages([
                    'amount' => "The amount exceeds the outstanding balance of £{$due}.",
                ]);
            }

            $this->assertReferenceIsFree($invoice, $data['reference'] ?? null);

            $payment = new Payment(
                Arr::only($data, ['method', 'amount', 'paid_at', 'reference', 'notes', 'meta'])
            );

            $payment->forceFill([
                'invoice_id' => $invoice->id,
                'status_id' => PaymentStatus::idForKey(PaymentStatus::KEY_COMPLETED),
                'type' => PaymentType::Payment,
                'amount' => $amount,
                'currency' => Payment::DEFAULT_CURRENCY,
                'created_by' => $actor->id,
            ])->save();

            $this->auditLogService->record(
                Log::ACTION_CREATE_PAYMENT,
                $actor,
                $payment,
                ['after' => $this->auditLogService->snapshot($payment)],
            );

            $this->balance->recalculate($invoice, $actor);

            return $payment;
        });
    }

    /**
     * Refuse a reference already used on this invoice, including by a
     * trashed row. Checked under the invoice lock, so two simultaneous
     * submissions cannot both pass.
     *
     * @throws ValidationException
     */
    protected function assertReferenceIsFree(Invoice $invoice, ?string $reference): void
    {
        if ($reference === null) {
            return;
        }

        $exists = Payment::withTrashed()
            ->where('invoice_id', $invoice->id)
            ->where('reference', $reference)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'reference' => 'A payment with that reference already exists on this invoice.',
            ]);
        }
    }
}

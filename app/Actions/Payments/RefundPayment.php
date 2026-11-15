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

class RefundPayment
{
    /**
     * Inject the required services.
     */
    public function __construct(
        protected readonly BalanceService $balance,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Refund all or part of a completed payment.
     *
     * Lock order is invoice first, then payment, the same as RecordPayment
     * and TransitionService, so two requests can never deadlock.
     *
     * @param  array<string, mixed>  $data  Prepared data from DataPreparationService
     *
     * @throws ValidationException
     */
    public function handle(Payment $payment, array $data, User $actor): Payment
    {
        return DB::transaction(function () use ($payment, $data, $actor): Payment {
            $invoice = Invoice::query()
                ->with('status')
                ->whereKey($payment->invoice_id)
                ->lockForUpdate()
                ->firstOrFail();

            $original = Payment::query()
                ->with('status')
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $original->isPayment() || ! $original->isCompleted()) {
                throw ValidationException::withMessages([
                    'payment' => 'Only a completed payment can be refunded.',
                ]);
            }

            $refundable = $this->balance->refundableAmount($original);
            $amount = $this->balance->money($data['amount']);

            if (bccomp($amount, '0', 2) !== 1 || bccomp($amount, $refundable, 2) === 1) {
                throw ValidationException::withMessages([
                    'amount' => "The refund must be between £0.01 and £{$refundable}.",
                ]);
            }

            if ($data['paid_at']->lt($original->paid_at)) {
                throw ValidationException::withMessages([
                    'paid_at' => 'A refund cannot be dated before the payment it reverses.',
                ]);
            }

            $reference = $data['reference'] ?? null;

            if (
                $reference !== null
                && Payment::withTrashed()
                    ->where('invoice_id', $invoice->id)
                    ->where('reference', $reference)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'reference' => 'A payment or refund with that reference already exists on this invoice.',
                ]);
            }

            $refund = new Payment(
                Arr::only($data, ['method', 'amount', 'paid_at', 'reference', 'notes'])
            );

            $refund->forceFill([
                'invoice_id' => $invoice->id,
                'parent_payment_id' => $original->id,
                'status_id' => PaymentStatus::idForKey(PaymentStatus::KEY_COMPLETED),
                'type' => PaymentType::Refund,
                'amount' => $amount,
                'currency' => $original->currency,
                'created_by' => $actor->id,
            ])->save();

            $this->auditLogService->record(
                Log::ACTION_REFUND_PAYMENT,
                $actor,
                $refund,
                [
                    'before' => $this->auditLogService->snapshot($original),
                    'after' => $this->auditLogService->snapshot($refund),
                ],
            );

            $this->balance->recalculate($invoice, $actor);

            return $refund;
        });
    }
}

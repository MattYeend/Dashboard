<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Log;
use App\Models\Payment;
use App\Models\PaymentStatus;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Invoices\BalanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitionService
{
    /**
     * Inject the required services.
     */
    public function __construct(
        protected readonly BalanceService $balance,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Complete a pending payment and apply it to the invoice.
     *
     * @throws ValidationException
     */
    public function complete(Payment $payment, User $actor): Payment
    {
        return DB::transaction(function () use ($payment, $actor): Payment {
            // Invoice first, then payment.
            $invoice = Invoice::query()
                ->with('status')
                ->whereKey($payment->invoice_id)
                ->lockForUpdate()
                ->firstOrFail();

            $locked = $this->lockPending($payment);

            if (! in_array($invoice->status?->title, BalanceService::PAYABLE_STATUSES, true)) {
                throw ValidationException::withMessages([
                    'invoice_id' => 'This invoice cannot receive payments in its current status.',
                ]);
            }

            if (bccomp($this->balance->money($locked->amount), $this->balance->balanceDue($invoice), 2) === 1) {
                throw ValidationException::withMessages([
                    'amount' => 'The payment now exceeds the outstanding balance of the invoice.',
                ]);
            }

            $this->move($locked, PaymentStatus::KEY_COMPLETED, $actor);

            $this->balance->recalculate($invoice, $actor);

            return $locked;
        });
    }

    /**
     * Mark a pending payment as failed. It was never counted, so the
     * invoice is untouched.
     *
     * @throws ValidationException
     */
    public function fail(Payment $payment, User $actor): Payment
    {
        return DB::transaction(function () use ($payment, $actor): Payment {
            $locked = $this->lockPending($payment);

            $this->move($locked, PaymentStatus::KEY_FAILED, $actor);

            return $locked;
        });
    }

    /**
     * Cancel a pending payment. It was never counted, so the invoice is
     * untouched.
     *
     * @throws ValidationException
     */
    public function cancel(Payment $payment, User $actor): Payment
    {
        return DB::transaction(function () use ($payment, $actor): Payment {
            $locked = $this->lockPending($payment);

            $this->move($locked, PaymentStatus::KEY_CANCELLED, $actor);

            return $locked;
        });
    }

    /**
     * Re-read the payment under a row lock and require it to be a pending
     * incoming payment.
     *
     * @throws ValidationException
     */
    protected function lockPending(Payment $payment): Payment
    {
        $locked = Payment::query()
            ->with('status')
            ->whereKey($payment->id)
            ->lockForUpdate()
            ->firstOrFail();

        if (! $locked->isPayment() || ! $locked->isStatus(PaymentStatus::KEY_PENDING)) {
            throw ValidationException::withMessages([
                'status' => 'Only a pending payment can change status.',
            ]);
        }

        return $locked;
    }

    /**
     * Move a locked payment to a new system status and audit it.
     */
    protected function move(Payment $payment, string $key, User $actor): void
    {
        $before = $this->auditLogService->snapshot($payment);

        $payment->forceFill([
            'status_id' => PaymentStatus::idForKey($key),
            'updated_by' => $actor->id,
        ])->save();

        $payment->unsetRelation('status');

        $this->auditLogService->record(
            Log::ACTION_TRANSITION_PAYMENT,
            $actor,
            $payment,
            [
                'before' => $before,
                'after' => $this->auditLogService->snapshot($payment),
            ],
        );
    }
}

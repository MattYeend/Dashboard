<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentStatus;
use App\Models\User;
use App\Services\Invoices\BalanceService;

class FormatterService
{
    /**
     * Inject the required services into the formatter service.
     */
    public function __construct(
        protected readonly BalanceService $balance,
    ) {}

    /**
     * Format a single payment with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Payment $payment, ?User $actor = null): array
    {
        $payment->loadMissing(['status', 'invoice', 'creator', 'updater', 'deleter', 'restorer']);

        $refundable = $this->balance->refundableAmount($payment);

        return [
            'id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'status_id' => $payment->status_id,
            'parent_payment_id' => $payment->parent_payment_id,
            'type' => $payment->type->value,
            'method' => $payment->method->value,
            'method_label' => $payment->method->label(),
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'reference' => $payment->reference,
            'stripe_payment_intent_id' => $payment->stripe_payment_intent_id,
            'paid_at' => $payment->paid_at,
            'notes' => $payment->notes,
            'meta' => $payment->meta,
            'created_at' => $payment->created_at,
            'updated_at' => $payment->updated_at,
            'deleted_at' => $payment->deleted_at,
            'restored_at' => $payment->restored_at,
            'status' => $this->formatStatus($payment->status),
            'invoice' => $this->formatInvoice($payment->invoice),
            'refundable_amount' => $payment->isPayment() ? $refundable : null,
            'can_refund' => $actor !== null
                && bccomp($refundable, '0', 2) === 1
                && $actor->can('refund', $payment),
            'is_locked' => $payment->isCompleted(),
            'creator' => $payment->creator ? ['id' => $payment->creator->id, 'name' => $payment->creator->name] : null,
            'updater' => $payment->updater ? ['id' => $payment->updater->id, 'name' => $payment->updater->name] : null,
            'deleter' => $payment->deleter ? ['id' => $payment->deleter->id, 'name' => $payment->deleter->name] : null,
            'restorer' => $payment->restorer ? ['id' => $payment->restorer->id, 'name' => $payment->restorer->name] : null,
        ];
    }

    /**
     * Format the payment status, or null when none is set.
     *
     * @return array<string, mixed>|null
     */
    private function formatStatus(?PaymentStatus $status): ?array
    {
        if ($status === null) {
            return null;
        }

        return [
            'id' => $status->id,
            'title' => $status->title,
            'system_key' => $status->system_key,
            'background_colour' => $status->background_colour,
            'text_colour' => $status->text_colour,
        ];
    }

    /**
     * Format the related invoice, or null when it is missing or soft-deleted.
     *
     * @return array<string, mixed>|null
     */
    private function formatInvoice(?Invoice $invoice): ?array
    {
        if ($invoice === null) {
            return null;
        }

        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total_amount' => $this->balance->poundsFromPence($invoice->total),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\Invoices\BalanceService;
use App\Services\UserRoleCheckerService;

class ActiveCheckerService
{
    /**
     * Inject the required services into the active checker service.
     */
    public function __construct(
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if the payment is active (not soft-deleted).
     */
    public function isActive(Payment $payment): bool
    {
        return ! $payment->trashed();
    }

    /**
     * Check if the payment is soft-deleted.
     */
    public function isTrashed(Payment $payment): bool
    {
        return $payment->trashed();
    }

    /**
     * Completed payments and every completed refund are locked: money has
     * moved and the row is a financial record.
     */
    public function isLocked(Payment $payment): bool
    {
        return $payment->isCompleted();
    }

    /**
     * Check if the payment can be edited.
     */
    public function canBeModified(Payment $payment): bool
    {
        return $this->isActive($payment) && ! $this->isLocked($payment);
    }

    /**
     * Check if the payment can be soft deleted.
     */
    public function canBeDeleted(Payment $payment): bool
    {
        return $this->canBeModified($payment);
    }

    /**
     * Check if the payment is soft-deleted and can be restored.
     */
    public function canBeRestoredOrForceDeleted(Payment $payment): bool
    {
        return $this->isTrashed($payment);
    }

    /**
     * Check if the payment can be permanently deleted. A completed row can
     * never be, even if it somehow became trashed.
     */
    public function canBeForceDeleted(Payment $payment): bool
    {
        return $this->isTrashed($payment) && ! $this->isLocked($payment);
    }

    /**
     * Check if the payment can be refunded: a live, completed, incoming
     * payment.
     */
    public function canBeRefunded(Payment $payment): bool
    {
        return $this->isActive($payment)
            && $payment->isPayment()
            && $payment->isCompleted();
    }

    /**
     * Check if the invoice is in a status that can receive payments.
     */
    public function invoiceAcceptsPayments(Invoice $invoice): bool
    {
        $invoice->loadMissing('status');

        return in_array($invoice->status?->title, BalanceService::PAYABLE_STATUSES, true);
    }

    /**
     * Check if user can modify the payment, or restore/force-delete it,
     * based on its state.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Payment $target,
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

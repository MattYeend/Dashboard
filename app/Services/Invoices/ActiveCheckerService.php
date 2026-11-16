<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Models\User;
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
     * Check if invoice is active (not soft-deleted).
     */
    public function isActive(Invoice $invoice): bool
    {
        return ! $invoice->trashed();
    }

    /**
     * Check if invoice is soft-deleted.
     */
    public function isTrashed(Invoice $invoice): bool
    {
        return $invoice->trashed();
    }

    /**
     * Check if invoice is active and can be updated or deleted.
     */
    public function canBeModified(Invoice $invoice): bool
    {
        return $this->isActive($invoice);
    }

    /**
     * Check if invoice is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(Invoice $invoice): bool
    {
        return $this->isTrashed($invoice);
    }

    /**
     * Check if the user can perform an action on the invoice.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Invoice $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'delete' => $this->roleChecker->isAdmin($actor) && $this->canBeDeleted($target),
            'forceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeForceDeleted($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }

    /**
     * Check if the invoice has any payments, including soft-deleted ones.
     */
    public function hasPayments(Invoice $invoice): bool
    {
        if (isset($invoice->payments_count)) {
            return $invoice->payments_count > 0;
        }

        return $invoice->payments()->withTrashed()->exists();
    }

    /**
     * Check if invoice is active and has no payments, so it can be soft-deleted.
     */
    public function canBeDeleted(Invoice $invoice): bool
    {
        return $this->isActive($invoice) && ! $this->hasPayments($invoice);
    }

    /**
     * Check if invoice is soft-deleted and has no payments, so it can be force-deleted.
     */
    public function canBeForceDeleted(Invoice $invoice): bool
    {
        return $this->isTrashed($invoice) && ! $this->hasPayments($invoice);
    }
}

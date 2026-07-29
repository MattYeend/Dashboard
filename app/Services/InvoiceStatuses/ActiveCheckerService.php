<?php

namespace App\Services\InvoiceStatuses;

use App\Models\InvoiceStatus;
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
     * Check if invoice status is active (not soft-deleted).
     */
    public function isActive(InvoiceStatus $invoiceStatus): bool
    {
        return ! $invoiceStatus->trashed();
    }

    /**
     * Check if invoice status is soft-deleted.
     */
    public function isTrashed(InvoiceStatus $invoiceStatus): bool
    {
        return $invoiceStatus->trashed();
    }

    /**
     * Check if invoice status is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(InvoiceStatus $invoiceStatus): bool
    {
        return $this->isActive($invoiceStatus);
    }

    /**
     * Check if invoice status is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        InvoiceStatus $invoiceStatus
    ): bool {
        return $this->isTrashed($invoiceStatus);
    }

    /**
     * Check if user can modify invoice status (update/delete) or restore/force-delete
     * invoice status based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        InvoiceStatus $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

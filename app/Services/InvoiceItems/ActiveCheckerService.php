<?php

namespace App\Services\InvoiceItems;

use App\Models\InvoiceItem;
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
     * Check if invoiceItem is active (not soft-deleted).
     */
    public function isActive(
        InvoiceItem $invoiceItem
    ): bool {
        return ! $invoiceItem->trashed();
    }

    /**
     * Check if invoiceItem is soft-deleted.
     */
    public function isTrashed(
        InvoiceItem $invoiceItem
    ): bool {
        return $invoiceItem->trashed();
    }

    /**
     * Check if invoiceItem is active and can be updated or deleted.
     */
    public function canBeModified(
        InvoiceItem $invoiceItem
    ): bool {
        return $this->isActive(
            $invoiceItem
        );
    }

    /**
     * Check if invoiceItem is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        InvoiceItem $invoiceItem
    ): bool {
        return $this->isTrashed(
            $invoiceItem
        );
    }

    /**
     * Check if the user can perform an action on the invoiceItem.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        InvoiceItem $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

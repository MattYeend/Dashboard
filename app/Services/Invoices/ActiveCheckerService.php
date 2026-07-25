<?php

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
    public function isActive(
        Invoice $invoice
    ): bool {
        return ! $invoice->trashed();
    }

    /**
     * Check if invoice is soft-deleted.
     */
    public function isTrashed(
        Invoice $invoice
    ): bool {
        return $invoice->trashed();
    }

    /**
     * Check if invoice is active and can be updated or deleted.
     */
    public function canBeModified(
        Invoice $invoice
    ): bool {
        return $this->isActive(
            $invoice
        );
    }

    /**
     * Check if invoice is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        Invoice $invoice
    ): bool {
        return $this->isTrashed(
            $invoice
        );
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
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

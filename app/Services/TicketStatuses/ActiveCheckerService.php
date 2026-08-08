<?php

namespace App\Services\TicketStatuses;

use App\Models\TicketStatus;
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
     * Check if ticketStatus is active (not soft-deleted).
     */
    public function isActive(TicketStatus $ticketStatus): bool
    {
        return ! $ticketStatus->trashed();
    }

    /**
     * Check if ticketStatus is soft-deleted.
     */
    public function isTrashed(TicketStatus $ticketStatus): bool
    {
        return $ticketStatus->trashed();
    }

    /**
     * Check if ticketStatus is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(TicketStatus $ticketStatus): bool
    {
        return $this->isActive($ticketStatus);
    }

    /**
     * Check if ticketStatus is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        TicketStatus $ticketStatus
    ): bool {
        return $this->isTrashed($ticketStatus);
    }

    /**
     * Check if user can modify ticketStatus (update/delete) or restore/force-delete
     * ticketStatus based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        TicketStatus $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

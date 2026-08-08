<?php

namespace App\Services\TicketPriorities;

use App\Models\TicketPriority;
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
     * Check if ticketPriority is active (not soft-deleted).
     */
    public function isActive(TicketPriority $ticketPriority): bool
    {
        return ! $ticketPriority->trashed();
    }

    /**
     * Check if ticketPriority is soft-deleted.
     */
    public function isTrashed(TicketPriority $ticketPriority): bool
    {
        return $ticketPriority->trashed();
    }

    /**
     * Check if ticketPriority is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(TicketPriority $ticketPriority): bool
    {
        return $this->isActive($ticketPriority);
    }

    /**
     * Check if ticketPriority is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        TicketPriority $ticketPriority
    ): bool {
        return $this->isTrashed($ticketPriority);
    }

    /**
     * Check if user can modify ticketPriority (update/delete) or restore/force-delete
     * ticketPriority based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        TicketPriority $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

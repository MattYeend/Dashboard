<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
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
     * Check if ticket is active (not soft-deleted).
     */
    public function isActive(Ticket $ticket): bool
    {
        return ! $ticket->trashed();
    }

    /**
     * Check if ticket is soft-deleted.
     */
    public function isTrashed(Ticket $ticket): bool
    {
        return $ticket->trashed();
    }

    /**
     * Check if ticket is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(Ticket $ticket): bool
    {
        return $this->isActive($ticket);
    }

    /**
     * Check if ticket is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        Ticket $ticket
    ): bool {
        return $this->isTrashed($ticket);
    }

    /**
     * Check if user can modify ticket (update/delete) or restore/force-delete
     * ticket based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Ticket $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

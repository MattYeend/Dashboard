<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    /**
     * Inject the required services into the policy authorisation service.
     */
    public function __construct(
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if user is a regular user, admin, or super admin.
     */
    public function isUser(User $user): bool
    {
        return $this->roleChecker->isUser($user);
    }

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    /**
     * Check if ticket is active (not soft-deleted).
     */
    public function isActive(Ticket $ticket): bool
    {
        return $this->activeChecker->isActive($ticket);
    }

    /**
     * Check if ticket is soft-deleted.
     */
    public function isTrashed(Ticket $ticket): bool
    {
        return $this->activeChecker->isTrashed($ticket);
    }

    /**
     * Determine whether the user can view any tickets.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any tickets');
    }

    /**
     * Determine whether the user can create tickets.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create tickets');
    }

    /**
     * Determine whether the user can view the ticket.
     */
    public function canView(User $actor, Ticket $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view tickets')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the ticket.
     */
    public function canUpdate(User $actor, Ticket $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit tickets')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the ticket.
     */
    public function canDelete(User $actor, Ticket $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete tickets')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the ticket.
     */
    public function canRestore(User $actor, Ticket $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore tickets')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the ticket.
     */
    public function canForceDelete(User $actor, Ticket $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }

    /**
     * Determine whether the user can import tickets.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import tickets');
    }

    /**
     * Determine whether the user can export tickets.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export tickets');
    }

    /**
     * Determine whether the user can assign the ticket.
     */
    public function canAssign(User $actor, Ticket $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('assign ticket')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the ticket was created by a user who outranks the actor.
     *
     * Prevents admins from managing tickets created by super admins.
     */
    private function targetOutranksActor(User $actor, Ticket $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $creator = $target->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($creator);
    }
}

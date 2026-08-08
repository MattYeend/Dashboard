<?php

namespace App\Services\TicketStatuses;

use App\Models\TicketStatus;
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
     * Check if ticketStatus is active (not soft-deleted).
     */
    public function isActive(TicketStatus $ticketStatus): bool
    {
        return $this->activeChecker->isActive($ticketStatus);
    }

    /**
     * Check if ticketStatus is soft-deleted.
     */
    public function isTrashed(TicketStatus $ticketStatus): bool
    {
        return $this->activeChecker->isTrashed($ticketStatus);
    }

    /**
     * Determine whether the user can view any ticket statuses.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view ticket statuses');
    }

    /**
     * Determine whether the user can create ticket statuses.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create ticket statuses');
    }

    /**
     * Determine whether the user can view the ticket status.
     */
    public function canView(User $actor, TicketStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view ticket statuses')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the ticket status.
     */
    public function canUpdate(User $actor, TicketStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit ticket statuses')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the ticket status.
     */
    public function canDelete(User $actor, TicketStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete ticket statuses')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the ticket status.
     */
    public function canRestore(User $actor, TicketStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore ticket statuses')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the ticket status.
     */
    public function canForceDelete(User $actor, TicketStatus $target): bool
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
     * Determine whether the user can assign the ticket status.
     */
    public function canAssign(User $actor, TicketStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('assign ticket statuses') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can import ticket statuses.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import ticket statuses');
    }

    /**
     * Determine whether the user can export ticket statuses.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export ticket statuses');
    }

    /**
     * Determine whether the ticket status was created by a user who outranks the actor.
     *
     * Prevents admins from managing ticket statuses created by super admins.
     */
    private function targetOutranksActor(User $actor, TicketStatus $target): bool
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

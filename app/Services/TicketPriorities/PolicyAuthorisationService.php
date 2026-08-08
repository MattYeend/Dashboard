<?php

namespace App\Services\TicketPriorities;

use App\Models\TicketPriority;
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
     * Check if ticketPriority is active (not soft-deleted).
     */
    public function isActive(TicketPriority $ticketPriority): bool
    {
        return $this->activeChecker->isActive($ticketPriority);
    }

    /**
     * Check if ticketPriority is soft-deleted.
     */
    public function isTrashed(TicketPriority $ticketPriority): bool
    {
        return $this->activeChecker->isTrashed($ticketPriority);
    }

    /**
     * Determine whether the user can view any ticket priorities.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view ticket priorities');
    }

    /**
     * Determine whether the user can create ticket priorities.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create ticket priorities');
    }

    /**
     * Determine whether the user can view the ticket priority.
     */
    public function canView(User $actor, TicketPriority $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view ticket priorities')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the ticket priority.
     */
    public function canUpdate(User $actor, TicketPriority $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit ticket priorities')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the ticket priority.
     */
    public function canDelete(User $actor, TicketPriority $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete ticket priorities')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the ticket priority.
     */
    public function canRestore(User $actor, TicketPriority $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore ticket priorities')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the ticket priority.
     */
    public function canForceDelete(User $actor, TicketPriority $target): bool
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
     * Determine whether the user can assign the ticket priority.
     */
    public function canAssign(User $actor, TicketPriority $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('assign ticket priorities') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can import ticket priorities.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import ticket priorities');
    }

    /**
     * Determine whether the user can export ticket priorities.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export ticket priorities');
    }

    /**
     * Determine whether the ticket priority was created by a user who outranks the actor.
     *
     * Prevents admins from managing ticket priorities created by super admins.
     */
    private function targetOutranksActor(User $actor, TicketPriority $target): bool
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

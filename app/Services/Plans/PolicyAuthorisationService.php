<?php

namespace App\Services\Plans;

use App\Models\Plan;
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
     * Check if plan is active (not soft-deleted).
     */
    public function isActive(Plan $plan): bool
    {
        return $this->activeChecker->isActive($plan);
    }

    /**
     * Check if plan is soft-deleted.
     */
    public function isTrashed(Plan $plan): bool
    {
        return $this->activeChecker->isTrashed($plan);
    }

    /**
     * Determine whether the user can view the plan.
     */
    public function canView(User $actor, Plan $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->isAdmin($actor) && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the plan.
     */
    public function canUpdate(User $actor, Plan $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->isAdmin($actor) && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the plan.
     */
    public function canDelete(User $actor, Plan $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->isAdmin($actor) && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the plan.
     */
    public function canRestore(User $actor, Plan $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->isAdmin($actor) && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the plan.
     */
    public function canForceDelete(User $actor, Plan $target): bool
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
     * Determine whether the plan was created by a user who outranks the actor.
     *
     * Prevents admins from managing plans created by super admins.
     */
    private function targetOutranksActor(User $actor, Plan $target): bool
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

<?php

namespace App\Services\Deals;

use App\Models\Deal;
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
     * Check if deal is active (not soft-deleted).
     */
    public function isActive(Deal $deal): bool
    {
        return $this->activeChecker->isActive($deal);
    }

    /**
     * Check if deal is soft-deleted.
     */
    public function isTrashed(Deal $deal): bool
    {
        return $this->activeChecker->isTrashed($deal);
    }

    /**
     * Determine whether the user can view the deal.
     */
    public function canView(User $actor, Deal $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view deal')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the deal.
     */
    public function canUpdate(User $actor, Deal $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit deal')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the deal.
     */
    public function canDelete(User $actor, Deal $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete deal')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the deal.
     */
    public function canRestore(User $actor, Deal $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore deal')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the deal.
     */
    public function canForceDelete(User $actor, Deal $target): bool
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
     * Determine whether the user can import deals.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import deal');
    }

    /**
     * Determine whether the user can export deals.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export deal');
    }

    /**
     * Determine whether the user can assign the deal.
     */
    public function canAssign(User $actor, Deal $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('assign deal')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can change the status of the deal.
     */
    public function canChangeStatus(User $actor, Deal $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('change deal status')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the deal was created by a user who outranks the actor.
     *
     * Prevents admins from managing deals created by super admins.
     */
    private function targetOutranksActor(
        User $actor,
        Deal $target
    ): bool {
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

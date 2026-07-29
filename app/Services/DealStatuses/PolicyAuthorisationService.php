<?php

namespace App\Services\DealStatuses;

use App\Models\DealStatus;
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
     * Check if deal status is active (not soft-deleted).
     */
    public function isActive(DealStatus $dealStatus): bool
    {
        return $this->activeChecker->isActive($dealStatus);
    }

    /**
     * Check if deal status is soft-deleted.
     */
    public function isTrashed(DealStatus $dealStatus): bool
    {
        return $this->activeChecker->isTrashed($dealStatus);
    }

    /**
     * Determine whether the user can view any deal statuses.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view deal statuses');
    }

    /**
     * Determine whether the user can create deal statuses.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create deal statuses');
    }

    /**
     * Determine whether the user can view the deal status.
     */
    public function canView(User $actor, DealStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view deal statuses')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the deal status.
     */
    public function canUpdate(User $actor, DealStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit deal statuses')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the deal status.
     */
    public function canDelete(User $actor, DealStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete deal statuses')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the deal status.
     */
    public function canRestore(User $actor, DealStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore deal statuses')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the deal status.
     */
    public function canForceDelete(User $actor, DealStatus $target): bool
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
     * Determine whether the user can import deal statuses.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import deal statuses');
    }

    /**
     * Determine whether the user can export deal statuses.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export deal statuses');
    }

    /**
     * Determine whether the user can assign the deal status.
     */
    public function canAssign(User $actor, DealStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('assign deal statuses')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the deal status was created by a user who outranks the actor.
     *
     * Prevents admins from managing deal statuses created by super admins.
     */
    private function targetOutranksActor(
        User $actor,
        DealStatus $target
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

<?php

namespace App\Services\Users;

use App\Models\User;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    /**
     * Inject the required services into the policy authorisation service.
     *
     * @param ActiveCheckerService $activeChecker
     * @param UserRoleCheckerService $roleChecker
     */
    public function __construct(
        protected ActiveCheckerService $activeChecker,
        protected UserRoleCheckerService $roleChecker
    ) {
    }

    /**
     * Check if user is a regular user, admin, or super admin.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function isUser(User $user): bool
    {
        return $this->roleChecker->isUser($user);
    }

    /**
     * Check if user is admin or super admin.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    /**
     * Check if user is active (not soft-deleted).
     *
     * @param  User $user
     *
     * @return bool
     */
    public function isActive(User $user): bool
    {
        return $this->activeChecker->isActive($user);
    }

    /**
     * Check if user is soft-deleted.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function isTrashed(User $user): bool
    {
        return $this->activeChecker->isTrashed($user);
    }

    /**
     * Determine whether the actor can view the target user.
     *
     * @param  User $actor
     * @param  User $target
     *
     * @return bool
     */
    public function canView(User $actor, User $target): bool
    {
        return $this->isAdmin($actor) && $this->activeChecker->isActive(
            $target
        );
    }

    /**
     * Determine whether the actor can update the target user.
     *
     * @param  User $actor
     * @param  User $target
     *
     * @return bool
     */
    public function canUpdate(User $actor, User $target): bool
    {
        return $this->isAdmin($actor) && $this->activeChecker->isActive(
            $target
        );
    }

    /**
     * Determine whether the actor can delete the target user.
     *
     * @param  User $actor
     * @param  User $target
     *
     * @return bool
     */
    public function canDelete(User $actor, User $target): bool
    {
        return $this->isAdmin($actor) && $this->activeChecker->canBeModified(
            $target
        );
    }

    /**
     * Determine whether the actor can restore the target user.
     *
     * @param  User $actor
     * @param  User $target
     *
     * @return bool
     */
    public function canRestore(User $actor, User $target): bool
    {
        return $this->isAdmin($actor) &&
            $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the actor can permanently delete the target user.
     *
     * @param  User $actor
     * @param  User $target
     *
     * @return bool
     */
    public function canForceDelete(User $actor, User $target): bool
    {
        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }
}

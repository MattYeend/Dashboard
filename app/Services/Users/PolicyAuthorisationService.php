<?php

namespace App\Services\Users;

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
     * Check if user is active (not soft-deleted).
     */
    public function isActive(User $user): bool
    {
        return $this->activeChecker->isActive($user);
    }

    /**
     * Check if user is soft-deleted.
     */
    public function isTrashed(User $user): bool
    {
        return $this->activeChecker->isTrashed($user);
    }

    /**
     * Determine whether the actor can view any users.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view users');
    }

    /**
     * Determine whether the actor can create users.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create users');
    }

    /**
     * Determine whether the actor can view the target user.
     */
    public function canView(User $actor, User $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view users') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the actor can update the target user.
     */
    public function canUpdate(User $actor, User $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit users') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the actor can delete the target user.
     */
    public function canDelete(User $actor, User $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete users') && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the actor can restore the target user.
     */
    public function canRestore(User $actor, User $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore users') &&
            $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the actor can permanently delete the target user.
     */
    public function canForceDelete(User $actor, User $target): bool
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
     * Determine whether the actor can import users.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import users');
    }

    /**
     * Determine whether the actor can export users.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export users');
    }

    /**
     * Determine whether the target user outranks the acting user.
     *
     * A Super Admin cannot be managed by anyone other than another Super Admin.
     */
    private function targetOutranksActor(User $actor, User $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($target);
    }
}

<?php

namespace App\Services\Permissions;

use App\Models\Permission;
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
     * Check if permission is active (not soft-deleted).
     */
    public function isActive(Permission $permission): bool
    {
        return $this->activeChecker->isActive($permission);
    }

    /**
     * Check if permission is soft-deleted.
     */
    public function isTrashed(Permission $permission): bool
    {
        return $this->activeChecker->isTrashed($permission);
    }

    /**
     * Determine whether the user can view any permissions.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any permissions');
    }

    /**
     * Determine whether the user can create permissions.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create permissions');
    }

    /**
     * Determine whether the user can view the permission.
     */
    public function canView(User $actor, Permission $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view permissions')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the permission.
     */
    public function canUpdate(User $actor, Permission $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit permissions')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the permission.
     */
    public function canDelete(User $actor, Permission $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete permissions')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the permission.
     */
    public function canRestore(User $actor, Permission $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore permissions')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the permission.
     */
    public function canForceDelete(User $actor, Permission $target): bool
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
     * Determine whether the user can assign roles to the permission.
     */
    public function canAssign(User $actor, Permission $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('assign permissions')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the permission was created by a user who outranks the actor.
     *
     * Prevents admins from managing permissions created by super admins.
     */
    private function targetOutranksActor(User $actor, Permission $target): bool
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

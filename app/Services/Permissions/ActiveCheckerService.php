<?php

namespace App\Services\Permissions;

use App\Models\Permission;
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
     * Check if permission is active (not soft-deleted).
     */
    public function isActive(Permission $permission): bool
    {
        return ! $permission->trashed();
    }

    /**
     * Check if permission is soft-deleted.
     */
    public function isTrashed(Permission $permission): bool
    {
        return $permission->trashed();
    }

    /**
     * Check if permission is active and can be updated or deleted.
     */
    public function canBeModified(Permission $permission): bool
    {
        return $this->isActive($permission);
    }

    /**
     * Check if permission is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(Permission $permission): bool
    {
        return $this->isTrashed($permission);
    }

    /**
     * Check if the user can perform an action on the permission.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Permission $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

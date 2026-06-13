<?php

namespace App\Services\Users;

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
     * Check if user is active (not soft-deleted).
     */
    public function isActive(User $user): bool
    {
        return ! $user->trashed();
    }

    /**
     * Check if user is soft-deleted.
     */
    public function isTrashed(User $user): bool
    {
        return $user->trashed();
    }

    /**
     * Check if user is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(User $user): bool
    {
        return $this->isActive($user);
    }

    /**
     * Check if user is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        User $user
    ): bool {
        return $this->isTrashed($user);
    }

    /**
     * Check if user can modify user (update/delete) or restore/force-delete
     * user based on its active status.
     */
    public function canUserPerformAction(
        User $user,
        string $action,
        User $targetUser
    ): bool {
        if ($action === 'modify') {
            return $this->roleChecker->isAdmin($user) && $this->canBeModified(
                $targetUser
            );
        }

        if ($action === 'restoreOrForceDelete') {
            return $this->roleChecker->isAdmin($user) &&
                $this->canBeRestoredOrForceDeleted($targetUser);
        }

        throw new \InvalidArgumentException("Invalid action: {$action}");
    }
}

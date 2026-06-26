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
        User $actor,
        string $action,
        User $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

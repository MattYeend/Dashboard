<?php

namespace App\Services\TaskStatuses;

use App\Models\TaskStatus;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class ActiveCheckerService
{
    /**
     * Inject the required services into the active checker service.
     */
    public function __construct(
        protected UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if taskStatus is active (not soft-deleted).
     */
    public function isActive(TaskStatus $taskStatus): bool
    {
        return ! $taskStatus->trashed();
    }

    /**
     * Check if taskStatus is soft-deleted.
     */
    public function isTrashed(TaskStatus $taskStatus): bool
    {
        return $taskStatus->trashed();
    }

    /**
     * Check if taskStatus is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(TaskStatus $taskStatus): bool
    {
        return $this->isActive($taskStatus);
    }

    /**
     * Check if taskStatus is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        TaskStatus $taskStatus
    ): bool {
        return $this->isTrashed($taskStatus);
    }

    /**
     * Check if user can modify taskStatus (update/delete) or restore/force-delete
     * taskStatus based on its active status.
     */
    public function canUserPerformAction(
        TaskStatus $taskStatus,
        string $action,
        User $user
    ): bool {
        if ($action === 'modify') {
            return $this->roleChecker->isAdmin($user) && $this->canBeModified(
                $taskStatus
            );
        }

        if ($action === 'restoreOrForceDelete') {
            return $this->roleChecker->isAdmin($user) &&
                $this->canBeRestoredOrForceDeleted($taskStatus);
        }

        throw new \InvalidArgumentException("Invalid action: {$action}");
    }
}

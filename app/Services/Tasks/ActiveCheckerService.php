<?php

namespace App\Services\Tasks;

use App\Models\Task;
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
     * Check if task is active (not soft-deleted).
     */
    public function isActive(Task $task): bool
    {
        return ! $task->trashed();
    }

    /**
     * Check if task is soft-deleted.
     */
    public function isTrashed(Task $task): bool
    {
        return $task->trashed();
    }

    /**
     * Check if task is active and can be updated or deleted.
     */
    public function canBeModified(Task $task): bool
    {
        return $this->isActive($task);
    }

    /**
     * Check if task is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(Task $task): bool
    {
        return $this->isTrashed($task);
    }

    /**
     * Check if the user can perform an action on the task.
     */
    public function canUserPerformAction(
        Task $task,
        string $action,
        User $user
    ): bool {
        if ($action === 'modify') {
            return $this->roleChecker->isAdmin($user) && $this->canBeModified($task);
        }

        if ($action === 'restoreOrForceDelete') {
            return $this->roleChecker->isAdmin($user) &&
                $this->canBeRestoredOrForceDeleted($task);
        }

        throw new \InvalidArgumentException("Invalid action: {$action}");
    }
}

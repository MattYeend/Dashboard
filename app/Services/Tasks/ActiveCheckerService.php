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
        protected readonly UserRoleCheckerService $roleChecker
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
        User $actor,
        string $action,
        Task $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

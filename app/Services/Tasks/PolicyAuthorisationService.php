<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    /**
     * Inject the required services into the policy authorisation service.
     */
    public function __construct(
        protected ActiveCheckerService $activeChecker,
        protected UserRoleCheckerService $roleChecker
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
     * Check if task is active (not soft-deleted).
     */
    public function isActive(Task $task): bool
    {
        return $this->activeChecker->isActive($task);
    }

    /**
     * Check if task is soft-deleted.
     */
    public function isTrashed(Task $task): bool
    {
        return $this->activeChecker->isTrashed($task);
    }

    /**
     * Determine whether the user can view the task.
     */
    public function canView(User $user, Task $task): bool
    {
        return $this->isAdmin($user) && $this->activeChecker->isActive($task);
    }

    /**
     * Determine whether the user can update the task.
     */
    public function canUpdate(User $user, Task $task): bool
    {
        return $this->isAdmin($user) && $this->activeChecker->isActive($task);
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function canDelete(User $user, Task $task): bool
    {
        return $this->isAdmin($user) && $this->activeChecker->canBeModified($task);
    }

    /**
     * Determine whether the user can restore the task.
     */
    public function canRestore(User $user, Task $task): bool
    {
        return $this->isAdmin($user) &&
            $this->activeChecker->canBeRestoredOrForceDeleted($task);
    }

    /**
     * Determine whether the user can permanently delete the task.
     */
    public function canForceDelete(User $user, Task $task): bool
    {
        return $this->activeChecker->canUserPerformAction(
            $task,
            'restoreOrForceDelete',
            $user
        );
    }
}

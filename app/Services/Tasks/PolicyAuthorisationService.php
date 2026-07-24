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
     * Determine whether the user can view any tasks.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any task');
    }

    /**
     * Determine whether the user can create tasks.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create task');
    }

    /**
     * Determine whether the user can view the task.
     */
    public function canView(User $actor, Task $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view task') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the task.
     */
    public function canUpdate(User $actor, Task $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit task') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function canDelete(User $actor, Task $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete task') && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the task.
     */
    public function canRestore(User $actor, Task $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore task') && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the task.
     */
    public function canForceDelete(User $actor, Task $target): bool
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
     * Determine whether the task was created by a user who outranks the actor.
     *
     * Prevents admins from managing tasks created by super admins.
     */
    private function targetOutranksActor(User $actor, Task $target): bool
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

    /**
     * Determine whether the user can assign the task to another user.
     */
    public function canAssign(User $actor, Task $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('assign task') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can change the task's status.
     */
    public function canChangeStatus(User $actor, Task $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('change task status') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can import tasks.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import task');
    }

    /**
     * Determine whether the user can export tasks.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export task');
    }
}

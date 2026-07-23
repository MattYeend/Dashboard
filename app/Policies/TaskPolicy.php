<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Services\Tasks\PolicyAuthorisationService;

class TaskPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any tasks.
     */
    public function viewAny(
        User $user
    ): bool {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can view the task.
     */
    public function view(
        User $user,
        Task $task
    ): bool {
        return $this->authorisationService->canView(
            $user,
            $task
        );
    }

    /**
     * Determine whether the user can create tasks.
     */
    public function create(
        User $user
    ): bool {
        return $this->authorisationService->isAdmin(
            $user
        );
    }

    /**
     * Determine whether the user can update the task.
     */
    public function update(
        User $user,
        Task $task
    ): bool {
        return $this->authorisationService->canUpdate(
            $user,
            $task
        );
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function delete(
        User $user,
        Task $task
    ): bool {
        return $this->authorisationService->canDelete(
            $user,
            $task
        );
    }

    /**
     * Determine whether the user can restore the task.
     */
    public function restore(
        User $user,
        Task $task
    ): bool {
        return $this->authorisationService->canRestore(
            $user,
            $task
        );
    }

    /**
     * Determine whether the user can permanently delete the task.
     */
    public function forceDelete(
        User $user,
        Task $task
    ): bool {
        return $this->authorisationService->canForceDelete(
            $user,
            $task
        );
    }

    /**
     * Determine whether the user can import models.
     */
    public function import(
        User $user
    ): bool {
        return $this->authorisationService->isAdmin(
            $user
        );
    }

    /**
     * Determine whether the user can export models.
     */
    public function export(
        User $user
    ): bool {
        return $this->authorisationService->isUser(
            $user
        );
    }

    /**
     * Determine whether the user can assign the model.
     */
    public function assign(
        User $user,
        Task $task
    ): bool {
        return $this->authorisationService->canAssign(
            $user,
            $task
        );
    }

    /**
     * Determine whether the user can change the model.
     */
    public function changeStatus(
        User $user,
        Task $task
    ): bool {
        return $this->authorisationService->canChangeStatus(
            $user,
            $task
        );
    }
}

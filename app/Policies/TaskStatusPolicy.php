<?php

namespace App\Policies;

use App\Models\TaskStatus;
use App\Models\User;
use App\Services\TaskStatuses\PolicyAuthorisationService;

class TaskStatusPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(
        User $user
    ): bool {
        return $this->authorisationService->isAdmin(
            $user
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(
        User $user,
        TaskStatus $taskStatus
    ): bool {
        return $this->authorisationService->canView(
            $user,
            $taskStatus
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(
        User $user
    ): bool {
        return $this->authorisationService->isAdmin(
            $user
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(
        User $user,
        TaskStatus $taskStatus
    ): bool {
        return $this->authorisationService->canUpdate(
            $user,
            $taskStatus
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(
        User $user,
        TaskStatus $taskStatus
    ): bool {
        return $this->authorisationService->canDelete(
            $user,
            $taskStatus
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(
        User $user,
        TaskStatus $taskStatus
    ): bool {
        return $this->authorisationService->canRestore(
            $user,
            $taskStatus
        );
    }

    /**
     * Determine whether the user can permanently delete the models.
     */
    public function forceDelete(
        User $user,
        TaskStatus $taskStatus
    ): bool {
        return $this->authorisationService->canForceDelete(
            $user,
            $taskStatus
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
     * Determine whether the user can assign the task status.
     */
    public function assign(
        User $user,
        TaskStatus $taskStatus
    ): bool {
        return $this->authorisationService->canAssign(
            $user,
            $taskStatus
        );
    }
}

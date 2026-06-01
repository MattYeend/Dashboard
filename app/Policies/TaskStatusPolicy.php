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
     * Determine whether the user can view any task statuses.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can view the task status.
     */
    public function view(User $user, TaskStatus $taskStatus): bool
    {
        return $this->authorisationService->canView($user, $taskStatus);
    }

    /**
     * Determine whether the user can create task statuses.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can update the task status.
     */
    public function update(User $user, TaskStatus $taskStatus): bool
    {
        return $this->authorisationService->canUpdate($user, $taskStatus);
    }

    /**
     * Determine whether the user can delete the task status.
     */
    public function delete(User $user, TaskStatus $taskStatus): bool
    {
        return $this->authorisationService->canDelete($user, $taskStatus);
    }

    /**
     * Determine whether the user can restore the task status.
     */
    public function restore(User $user, TaskStatus $taskStatus): bool
    {
        return $this->authorisationService->canRestore($user, $taskStatus);
    }

    /**
     * Determine whether the user can permanently delete the task status.
     */
    public function forceDelete(User $user, TaskStatus $taskStatus): bool
    {
        return $this->authorisationService->canForceDelete($user, $taskStatus);
    }
}

<?php

namespace App\Services\TaskStatuses;

use App\Models\TaskStatus;
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
     * Check if taskStatus is active (not soft-deleted).
     */
    public function isActive(TaskStatus $taskStatus): bool
    {
        return $this->activeChecker->isActive($taskStatus);
    }

    /**
     * Check if taskStatus is soft-deleted.
     */
    public function isTrashed(TaskStatus $taskStatus): bool
    {
        return $this->activeChecker->isTrashed($taskStatus);
    }

    /**
     * Determine whether the user can view the model.
     * Only admins can view company contacts.
     */
    public function canView(User $user, TaskStatus $taskStatus): bool
    {
        return $this->isAdmin($user) && $this->activeChecker->isActive(
            $taskStatus
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function canUpdate(User $user, TaskStatus $taskStatus): bool
    {
        return $this->isAdmin($user) && $this->activeChecker->isActive(
            $taskStatus
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function canDelete(User $user, TaskStatus $taskStatus): bool
    {
        return $this->isAdmin($user) && $this->activeChecker->canBeModified(
            $taskStatus
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function canRestore(User $user, TaskStatus $taskStatus): bool
    {
        return $this->isAdmin($user) &&
            $this->activeChecker->canBeRestoredOrForceDeleted($taskStatus);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function canForceDelete(User $user, TaskStatus $taskStatus): bool
    {
        return $this->isAdmin($user)
            && $this->activeChecker->canUserPerformAction(
                $user,
                'restoreOrForceDelete',
                $taskStatus
            );
    }
}

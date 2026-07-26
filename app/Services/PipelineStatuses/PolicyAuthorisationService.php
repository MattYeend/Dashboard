<?php

namespace App\Services\PipelineStatuses;

use App\Models\PipelineStatus;
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
     * Check if pipelineStatus is active (not soft-deleted).
     */
    public function isActive(PipelineStatus $pipelineStatus): bool
    {
        return $this->activeChecker->isActive($pipelineStatus);
    }

    /**
     * Check if pipelineStatus is soft-deleted.
     */
    public function isTrashed(PipelineStatus $pipelineStatus): bool
    {
        return $this->activeChecker->isTrashed($pipelineStatus);
    }

    /**
     * Determine whether the user can view any pipeline statuses.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view pipeline statuses');
    }

    /**
     * Determine whether the user can create pipeline statuses.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create pipeline statuses');
    }

    /**
     * Determine whether the user can view the pipeline status.
     */
    public function canView(User $actor, PipelineStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view pipeline statuses')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the pipeline status.
     */
    public function canUpdate(User $actor, PipelineStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit pipeline statuses')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the pipeline status.
     */
    public function canDelete(User $actor, PipelineStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete pipeline statuses')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the pipeline status.
     */
    public function canRestore(User $actor, PipelineStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore pipeline statuses')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the pipeline status.
     */
    public function canForceDelete(User $actor, PipelineStatus $target): bool
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
     * Determine whether the user can import pipeline statuses.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import pipeline statuses');
    }

    /**
     * Determine whether the user can export pipeline statuses.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export pipeline statuses');
    }

    /**
     * Determine whether the user can assign the pipeline status.
     */
    public function canAssign(User $actor, PipelineStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('assign pipeline statuses')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the pipeline status was created by a user who outranks the actor.
     *
     * Prevents admins from managing pipeline statuses created by super admins.
     */
    private function targetOutranksActor(
        User $actor,
        PipelineStatus $target
    ): bool {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $creator = $target->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($creator);
    }
}
<?php

namespace App\Services\PipelineStages;

use App\Models\PipelineStage;
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
     * Check if pipeline stage is active (not soft-deleted).
     */
    public function isActive(PipelineStage $pipelineStage): bool
    {
        return $this->activeChecker->isActive($pipelineStage);
    }

    /**
     * Check if pipeline stage is soft-deleted.
     */
    public function isTrashed(PipelineStage $pipelineStage): bool
    {
        return $this->activeChecker->isTrashed($pipelineStage);
    }

    /**
     * Determine whether the user can view any pipeline stages.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any pipeline stage');
    }

    /**
     * Determine whether the user can create pipeline stages.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create pipeline stage');
    }

    /**
     * Determine whether the user can view the pipeline stage.
     */
    public function canView(User $actor, PipelineStage $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view pipeline stage')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the pipeline stage.
     */
    public function canUpdate(User $actor, PipelineStage $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit pipeline stage')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the pipeline stage.
     */
    public function canDelete(User $actor, PipelineStage $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete pipeline stage')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the pipeline stage.
     */
    public function canRestore(User $actor, PipelineStage $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore pipeline stage')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the pipeline stage.
     */
    public function canForceDelete(User $actor, PipelineStage $target): bool
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
     * Determine whether the user can import pipeline stages.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import pipeline stage');
    }

    /**
     * Determine whether the user can export pipeline stages.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export pipeline stage');
    }

    /**
     * Determine whether the user can assign the pipeline stage.
     */
    public function canAssign(User $actor, PipelineStage $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('assign pipeline stage')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the pipeline stage was created by a user who outranks the actor.
     *
     * Prevents admins from managing pipeline stages created by super admins.
     */
    private function targetOutranksActor(
        User $actor,
        PipelineStage $target
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

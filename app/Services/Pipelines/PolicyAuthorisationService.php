<?php

namespace App\Services\Pipelines;

use App\Models\Pipeline;
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
     * Check if pipeline is active (not soft-deleted).
     */
    public function isActive(Pipeline $pipeline): bool
    {
        return $this->activeChecker->isActive($pipeline);
    }

    /**
     * Check if pipeline is soft-deleted.
     */
    public function isTrashed(Pipeline $pipeline): bool
    {
        return $this->activeChecker->isTrashed($pipeline);
    }

    /**
     * Determine whether the user can view any pipelines.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view pipelines');
    }

    /**
     * Determine whether the user can create pipelines.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create pipelines');
    }

    /**
     * Determine whether the user can view the pipeline.
     */
    public function canView(User $actor, Pipeline $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view pipelines')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the pipeline.
     */
    public function canUpdate(User $actor, Pipeline $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit pipelines')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the pipeline.
     */
    public function canDelete(User $actor, Pipeline $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete pipelines')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the pipeline.
     */
    public function canRestore(User $actor, Pipeline $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore pipelines')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the pipeline.
     */
    public function canForceDelete(User $actor, Pipeline $target): bool
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
     * Determine whether the user can import pipelines.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import pipelines');
    }

    /**
     * Determine whether the user can export pipelines.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export pipelines');
    }

    /**
     * Determine whether the user can assign the pipeline.
     */
    public function canAssign(User $actor, Pipeline $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('assign pipeline')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the pipeline was created by a user who outranks the actor.
     *
     * Prevents admins from managing pipeline created by super admins.
     */
    private function targetOutranksActor(
        User $actor,
        Pipeline $target
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

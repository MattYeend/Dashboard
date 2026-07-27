<?php

namespace App\Services\PipelineStages;

use App\Models\PipelineStage;
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
     * Check if pipelineStage is active (not soft-deleted).
     */
    public function isActive(PipelineStage $pipelineStage): bool
    {
        return ! $pipelineStage->trashed();
    }

    /**
     * Check if pipelineStage is soft-deleted.
     */
    public function isTrashed(PipelineStage $pipelineStage): bool
    {
        return $pipelineStage->trashed();
    }

    /**
     * Check if pipelineStage is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(PipelineStage $pipelineStage): bool
    {
        return $this->isActive($pipelineStage);
    }

    /**
     * Check if pipelineStage is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(PipelineStage $pipelineStage): bool
    {
        return $this->isTrashed($pipelineStage);
    }

    /**
     * Check if user can modify pipelineStage (update/delete) or restore/force-delete
     * pipelineStage based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        PipelineStage $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

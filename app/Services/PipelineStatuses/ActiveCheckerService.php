<?php

namespace App\Services\PipelineStatuses;

use App\Models\PipelineStatus;
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
     * Check if pipelineStatus is active (not soft-deleted).
     */
    public function isActive(PipelineStatus $pipelineStatus): bool
    {
        return ! $pipelineStatus->trashed();
    }

    /**
     * Check if pipelineStatus is soft-deleted.
     */
    public function isTrashed(PipelineStatus $pipelineStatus): bool
    {
        return $pipelineStatus->trashed();
    }

    /**
     * Check if pipelineStatus is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(PipelineStatus $pipelineStatus): bool
    {
        return $this->isActive($pipelineStatus);
    }

    /**
     * Check if pipelineStatus is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(PipelineStatus $pipelineStatus): bool 
    {
        return $this->isTrashed($pipelineStatus);
    }

    /**
     * Check if user can modify pipelineStatus (update/delete) or restore/force-delete
     * pipelineStatus based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        PipelineStatus $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
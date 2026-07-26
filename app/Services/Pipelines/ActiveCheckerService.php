<?php

namespace App\Services\Pipelines;

use App\Models\Pipeline;
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
     * Check if pipeline is active (not soft-deleted).
     */
    public function isActive(Pipeline $pipeline): bool
    {
        return ! $pipeline->trashed();
    }

    /**
     * Check if pipeline is soft-deleted.
     */
    public function isTrashed(Pipeline $pipeline): bool
    {
        return $pipeline->trashed();
    }

    /**
     * Check if pipeline is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(Pipeline $pipeline): bool
    {
        return $this->isActive($pipeline);
    }

    /**
     * Check if pipeline is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(Pipeline $pipeline): bool
    {
        return $this->isTrashed($pipeline);
    }

    /**
     * Check if user can modify pipeline (update/delete) or restore/force-delete
     * pipeline based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Pipeline $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

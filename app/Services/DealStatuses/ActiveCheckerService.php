<?php

namespace App\Services\DealStatuses;

use App\Models\DealStatus;
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
     * Check if deal status is active (not soft-deleted).
     */
    public function isActive(DealStatus $dealStatus): bool
    {
        return ! $dealStatus->trashed();
    }

    /**
     * Check if deal status is soft-deleted.
     */
    public function isTrashed(DealStatus $dealStatus): bool
    {
        return $dealStatus->trashed();
    }

    /**
     * Check if deal status is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(DealStatus $dealStatus): bool
    {
        return $this->isActive($dealStatus);
    }

    /**
     * Check if deal status is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        DealStatus $dealStatus
    ): bool {
        return $this->isTrashed($dealStatus);
    }

    /**
     * Check if user can modify deal status (update/delete) or restore/force-delete
     * deal status based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        DealStatus $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

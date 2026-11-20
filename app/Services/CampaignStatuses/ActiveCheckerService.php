<?php

declare(strict_types=1);

namespace App\Services\CampaignStatuses;

use App\Models\CampaignStatus;
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
     * Check if campaign status is active (not soft-deleted).
     */
    public function isActive(CampaignStatus $campaignStatus): bool
    {
        return ! $campaignStatus->trashed();
    }

    /**
     * Check if campaign status is soft-deleted.
     */
    public function isTrashed(CampaignStatus $campaignStatus): bool
    {
        return $campaignStatus->trashed();
    }

    /**
     * Check if campaign status is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(CampaignStatus $campaignStatus): bool
    {
        return $this->isActive($campaignStatus);
    }

    /**
     * Check if campaign status is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        CampaignStatus $campaignStatus
    ): bool {
        return $this->isTrashed($campaignStatus);
    }

    /**
     * Check if user can modify campaign status (update/delete) or restore/force-delete
     * campaign status based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        CampaignStatus $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

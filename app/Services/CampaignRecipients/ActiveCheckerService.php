<?php

declare(strict_types=1);

namespace App\Services\CampaignRecipients;

use App\Models\CampaignRecipient;
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
     * Check if campaign recipient is active (not soft-deleted).
     */
    public function isActive(CampaignRecipient $campaignRecipient): bool
    {
        return ! $campaignRecipient->trashed();
    }

    /**
     * Check if campaign recipient is soft-deleted.
     */
    public function isTrashed(CampaignRecipient $campaignRecipient): bool
    {
        return $campaignRecipient->trashed();
    }

    /**
     * Check if campaign recipient can be edited by the system.
     */
    public function isEditable(CampaignRecipient $campaignRecipient): bool
    {
        return $this->isActive($campaignRecipient);
    }

    /**
     * Check if campaign recipient can be soft-deleted.
     */
    public function canBeDeleted(CampaignRecipient $campaignRecipient): bool
    {
        return $this->isActive($campaignRecipient);
    }

    /**
     * Check if campaign recipient is active (not soft-deleted) and can be modified.
     */
    public function canBeModified(CampaignRecipient $campaignRecipient): bool
    {
        return $this->isActive($campaignRecipient);
    }

    /**
     * Check if campaign recipient is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        CampaignRecipient $campaignRecipient
    ): bool {
        return $this->isTrashed($campaignRecipient);
    }

    /**
     * Check if the user can modify, restore or force-delete the campaign
     * recipient based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        CampaignRecipient $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

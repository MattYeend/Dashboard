<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Models\Campaign;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class ActiveCheckerService
{
    /**
     * Status system keys in which a campaign may be edited.
     *
     * @var array<int, string>
     */
    private const EDITABLE = ['draft'];

    /**
     * Status system keys in which a campaign may be deleted.
     *
     * @var array<int, string>
     */
    private const DELETABLE = ['draft', 'cancelled', 'failed'];

    /**
     * Inject the required services into the active checker service.
     */
    public function __construct(
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Status keys in which a campaign may be edited.
     *
     * @return array<int, string>
     */
    public function editableStatuses(): array
    {
        return self::EDITABLE;
    }

    /**
     * Status keys in which a campaign may be deleted.
     *
     * @return array<int, string>
     */
    public function deletableStatuses(): array
    {
        return self::DELETABLE;
    }

    /**
     * Check if campaign is active (not soft-deleted).
     */
    public function isActive(Campaign $campaign): bool
    {
        return ! $campaign->trashed();
    }

    /**
     * Check if campaign is soft-deleted.
     */
    public function isTrashed(Campaign $campaign): bool
    {
        return $campaign->trashed();
    }

    /**
     * Check if campaign can be edited (active and in an editable status).
     */
    public function isEditable(Campaign $campaign): bool
    {
        return $this->isActive($campaign)
            && $this->hasKey($campaign, self::EDITABLE);
    }

    /**
     * Check if campaign can be soft-deleted (active and in a deletable status).
     */
    public function canBeDeleted(Campaign $campaign): bool
    {
        return $this->isActive($campaign)
            && $this->hasKey($campaign, self::DELETABLE);
    }

    /**
     * Check if campaign is active (not soft-deleted) and can be modified.
     */
    public function canBeModified(Campaign $campaign): bool
    {
        return $this->isActive($campaign);
    }

    /**
     * Check if campaign is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        Campaign $campaign
    ): bool {
        return $this->isTrashed($campaign);
    }

    /**
     * Check if the user can modify, restore or force-delete the campaign
     * based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Campaign $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }

    /**
     * Check whether the campaign's status has one of the given system keys.
     *
     * @param  array<int, string>  $keys
     */
    private function hasKey(Campaign $campaign, array $keys): bool
    {
        $campaign->loadMissing('status');

        return in_array($campaign->status?->system_key, $keys, true);
    }
}

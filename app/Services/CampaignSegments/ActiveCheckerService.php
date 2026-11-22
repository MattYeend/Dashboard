<?php

declare(strict_types=1);

namespace App\Services\CampaignSegments;

use App\Models\CampaignSegment;
use App\Models\User;
use App\Services\UserRoleCheckerService;
use Illuminate\Database\Eloquent\Builder;

class ActiveCheckerService
{
    /**
     * Campaign status keys that lock a segment against deletion.
     *
     * @var array<int, string>
     */
    private const LOCKING_CAMPAIGN_STATUSES = ['scheduled', 'sending'];

    /**
     * Inject the required services into the active checker service.
     */
    public function __construct(
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if the segment is active (not soft-deleted).
     */
    public function isActive(CampaignSegment $campaignSegment): bool
    {
        return ! $campaignSegment->trashed();
    }

    /**
     * Check if the segment is soft-deleted.
     */
    public function isTrashed(CampaignSegment $campaignSegment): bool
    {
        return $campaignSegment->trashed();
    }

    /**
     * Check if the segment is active and can be updated.
     */
    public function canBeModified(CampaignSegment $campaignSegment): bool
    {
        return $this->isActive($campaignSegment);
    }

    /**
     * Check if the segment is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(CampaignSegment $campaignSegment): bool
    {
        return $this->isTrashed($campaignSegment);
    }

    /**
     * Check if a scheduled or sending campaign is using the segment.
     */
    public function isLockedByActiveCampaign(CampaignSegment $campaignSegment): bool
    {
        return $campaignSegment->campaigns()
            ->whereHas(
                'status',
                static fn (Builder $query): Builder => $query->whereIn(
                    'system_key',
                    self::LOCKING_CAMPAIGN_STATUSES
                )
            )
            ->exists();
    }

    /**
     * Check if any campaign, including a soft-deleted one, still uses the segment.
     */
    public function isReferencedByCampaigns(CampaignSegment $campaignSegment): bool
    {
        return $campaignSegment->campaigns()->withTrashed()->exists();
    }

    /**
     * Check if the segment can be soft deleted.
     */
    public function canBeDeleted(CampaignSegment $campaignSegment): bool
    {
        return $this->canBeModified($campaignSegment)
            && ! $this->isLockedByActiveCampaign($campaignSegment);
    }

    /**
     * Check if the segment can be permanently deleted.
     */
    public function canBeForceDeleted(CampaignSegment $campaignSegment): bool
    {
        return $this->canBeRestoredOrForceDeleted($campaignSegment)
            && ! $this->isReferencedByCampaigns($campaignSegment);
    }

    /**
     * Check if the user can perform an action on the segment.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        CampaignSegment $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

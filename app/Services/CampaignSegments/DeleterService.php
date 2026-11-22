<?php

declare(strict_types=1);

namespace App\Services\CampaignSegments;

use App\Actions\DeleteResource;
use App\Models\CampaignSegment;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
        protected readonly ActiveCheckerService $activeChecker,
    ) {}

    /**
     * Soft delete a campaign segment.
     *
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function delete(CampaignSegment $campaignSegment, int $deletedBy, ?User $actor = null): bool
    {
        if ($this->activeChecker->isLockedByActiveCampaign($campaignSegment)) {
            throw new AuthorizationException(
                'This segment is used by a scheduled or sending campaign and cannot be deleted.'
            );
        }

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $campaignSegment,
            function (CampaignSegment $campaignSegment) use ($actor, $deletedBy): void {
                $campaignSegment->deleted_by = $deletedBy;
                $campaignSegment->deleted_at = now();
                $campaignSegment->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_CAMPAIGN_SEGMENT,
                    $actor,
                    $campaignSegment,
                    ['before' => $this->auditLogService->snapshot($campaignSegment)],
                );
            });
    }

    /**
     * Force delete a campaign segment (permanent deletion).
     *
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function forceDelete(CampaignSegment $campaignSegment, int $deletedBy): bool
    {
        if ($this->activeChecker->isReferencedByCampaigns($campaignSegment)) {
            throw new AuthorizationException(
                'This segment is still referenced by one or more campaigns and cannot be permanently deleted.'
            );
        }

        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $campaignSegment,
            function (CampaignSegment $campaignSegment) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_CAMPAIGN_SEGMENT,
                    $actor,
                    $campaignSegment,
                    ['before' => $this->auditLogService->snapshot($campaignSegment)],
                );
            });
    }

    /**
     * Delete multiple campaign segments.
     *
     * @param  array<int, int>  $campaignSegmentIds
     *
     * @throws \Exception
     */
    public function deleteMultiple(array $campaignSegmentIds, int $deletedBy): int
    {
        $count = 0;

        DB::transaction(function () use ($campaignSegmentIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $campaignSegments = CampaignSegment::whereIn('id', $campaignSegmentIds)->get();

            foreach ($campaignSegments as $campaignSegment) {
                if ($this->delete($campaignSegment, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

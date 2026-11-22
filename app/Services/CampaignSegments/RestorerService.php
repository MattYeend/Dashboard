<?php

declare(strict_types=1);

namespace App\Services\CampaignSegments;

use App\Actions\RestoreResource;
use App\Models\CampaignSegment;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted campaign segment.
     *
     * @throws \Exception
     */
    public function restore(
        CampaignSegment $campaignSegment,
        int $restoredBy,
        ?User $actor = null
    ): CampaignSegment {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $campaignSegment,
            function (CampaignSegment $campaignSegment) use ($actor, $restoredBy): void {
                $campaignSegment->restored_by = $restoredBy;
                $campaignSegment->restored_at = now();
                $campaignSegment->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_CAMPAIGN_SEGMENT,
                    $actor,
                    $campaignSegment,
                    ['before' => $this->auditLogService->snapshot($campaignSegment)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted campaign segments.
     *
     * @param  array<int, int>  $campaignSegmentIds
     * @return int Number of campaign segments restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(array $campaignSegmentIds, int $restoredBy): int
    {
        $count = 0;

        DB::transaction(function () use ($campaignSegmentIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, CampaignSegment> $campaignSegments */
            $campaignSegments = CampaignSegment::onlyTrashed()
                ->whereIn('id', $campaignSegmentIds)
                ->get();

            foreach ($campaignSegments as $campaignSegment) {
                $this->restore($campaignSegment, $restoredBy, $actor);
                $count++;
            }
        });

        return $count;
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Actions\RestoreResource;
use App\Models\Campaign;
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
     * Restore a soft-deleted campaign.
     *
     * @throws \Exception
     */
    public function restore(
        Campaign $campaign,
        int $restoredBy,
        ?User $actor = null,
    ): Campaign {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $campaign,
            function (Campaign $campaign) use ($actor, $restoredBy): void {
                $campaign->restored_by = $restoredBy;
                $campaign->restored_at = now();
                $campaign->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_CAMPAIGN,
                    $actor,
                    $campaign,
                    ['before' => $this->auditLogService->snapshot($campaign)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted campaigns.
     *
     * @param  array<int, int>  $campaignIds
     * @return int Number of campaigns restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $campaignIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($campaignIds, $restoredBy, &$count): void {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, Campaign> $campaigns */
            $campaigns = Campaign::onlyTrashed()
                ->whereIn('id', $campaignIds)
                ->get();

            foreach ($campaigns as $campaign) {
                $this->restore($campaign, $restoredBy, $actor);
                $count++;
            }
        });

        return $count;
    }
}

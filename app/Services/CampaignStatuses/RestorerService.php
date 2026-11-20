<?php

declare(strict_types=1);

namespace App\Services\CampaignStatuses;

use App\Actions\RestoreResource;
use App\Models\CampaignStatus;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the resorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted campaignStatus.
     *
     * @throws \Exception
     */
    public function restore(
        CampaignStatus $campaignStatus,
        int $restoredBy,
        ?User $actor = null,
    ): CampaignStatus {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $campaignStatus,
            function (CampaignStatus $campaignStatus) use ($actor, $restoredBy): void {
                $campaignStatus->restored_by = $restoredBy;
                $campaignStatus->restored_at = now();
                $campaignStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_CAMPAIGN_STATUS,
                    $actor,
                    $campaignStatus,
                    ['before' => $this->auditLogService->snapshot($campaignStatus)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted campaignStatuses.
     *
     * @return int Number of campaignStatuses restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $campaignStatusIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($campaignStatusIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,CampaignStatus> $campaignStatuses */
            $campaignStatuses = CampaignStatus::withTrashed()
                ->whereIn('id', $campaignStatusIds)
                ->get();

            foreach ($campaignStatuses as $campaignStatus) {
                if ($campaignStatus->trashed()) {
                    $this->restore($campaignStatus, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}

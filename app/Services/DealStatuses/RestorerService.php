<?php

namespace App\Services\DealStatuses;

use App\Actions\RestoreResource;
use App\Models\DealStatus;
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
     * Restore a soft-deleted dealStatus.
     *
     * @throws \Exception
     */
    public function restore(
        DealStatus $dealStatus,
        int $restoredBy,
        ?User $actor = null,
    ): DealStatus {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $dealStatus,
            function (DealStatus $dealStatus) use ($actor, $restoredBy): void {
                $dealStatus->restored_by = $restoredBy;
                $dealStatus->restored_at = now();
                $dealStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_DEAL_STATUS,
                    $actor,
                    $dealStatus,
                    ['before' => $this->auditLogService->snapshot($dealStatus)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted dealStatuses.
     *
     * @return int Number of dealStatuses restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $dealStatusIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($dealStatusIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,DealStatus> $dealStatuses */
            $dealStatuses = DealStatus::withTrashed()
                ->whereIn('id', $dealStatusIds)
                ->get();

            foreach ($dealStatuses as $dealStatus) {
                if ($dealStatus->trashed()) {
                    $this->restore($dealStatus, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}

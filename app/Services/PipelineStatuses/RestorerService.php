<?php

namespace App\Services\PipelineStatuses;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\PipelineStatus;
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
     * Restore a soft-deleted pipelineStatus.
     *
     * @throws \Exception
     */
    public function restore(
        PipelineStatus $pipelineStatus,
        int $restoredBy,
        ?User $actor = null,
    ): PipelineStatus {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $pipelineStatus,
            function (PipelineStatus $pipelineStatus) use ($actor, $restoredBy): void {
                $pipelineStatus->restored_by = $restoredBy;
                $pipelineStatus->restored_at = now();
                $pipelineStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_PIPELINE_STATUS,
                    $actor,
                    $pipelineStatus,
                    ['before' => $this->auditLogService->snapshot($pipelineStatus)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted pipelineStatuses.
     *
     * @return int Number of pipelineStatuses restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $pipelineStatusIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($pipelineStatusIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,PipelineStatus> $pipelineStatuses */
            $pipelineStatuses = PipelineStatus::withTrashed()
                ->whereIn('id', $pipelineStatusIds)
                ->get();

            foreach ($pipelineStatuses as $pipelineStatus) {
                if ($pipelineStatus->trashed()) {
                    $this->restore($pipelineStatus, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
<?php

namespace App\Services\PipelineStages;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\PipelineStage;
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
     * Restore a soft-deleted pipeline stage.
     *
     * @throws \Exception
     */
    public function restore(
        PipelineStage $pipelineStage,
        int $restoredBy,
        ?User $actor = null,
    ): PipelineStage {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $pipelineStage,
            function (PipelineStage $pipelineStage) use ($actor, $restoredBy): void {
                $pipelineStage->restored_by = $restoredBy;
                $pipelineStage->restored_at = now();
                $pipelineStage->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_PIPELINE,
                    $actor,
                    $pipelineStage,
                    ['before' => $this->auditLogService->snapshot($pipelineStage)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted pipelineStages.
     *
     * @return int Number of pipelineStages restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $pipelineIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($pipelineIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,PipelineStage> $pipelineStages */
            $pipelineStages = PipelineStage::withTrashed()
                ->whereIn('id', $pipelineIds)
                ->get();

            foreach ($pipelineStages as $pipelineStage) {
                if ($pipelineStage->trashed()) {
                    $this->restore($pipelineStage, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}

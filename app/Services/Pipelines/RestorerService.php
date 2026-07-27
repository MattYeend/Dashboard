<?php

namespace App\Services\Pipelines;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\Pipeline;
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
     * Restore a soft-deleted pipeline.
     *
     * @throws \Exception
     */
    public function restore(
        Pipeline $pipeline,
        int $restoredBy,
        ?User $actor = null,
    ): Pipeline {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $pipeline,
            function (Pipeline $pipeline) use ($actor, $restoredBy): void {
                $pipeline->restored_by = $restoredBy;
                $pipeline->restored_at = now();
                $pipeline->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_PIPELINE,
                    $actor,
                    $pipeline,
                    ['before' => $this->auditLogService->snapshot($pipeline)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted pipelines.
     *
     * @return int Number of pipelines restored
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

            /** @var Collection<int,Pipeline> $pipelines */
            $pipelines = Pipeline::withTrashed()
                ->whereIn('id', $pipelineIds)
                ->get();

            foreach ($pipelines as $pipeline) {
                if ($pipeline->trashed()) {
                    $this->restore($pipeline, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}

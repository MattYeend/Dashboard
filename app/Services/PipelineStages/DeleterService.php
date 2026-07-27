<?php

namespace App\Services\PipelineStages;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\PipelineStage;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete a pipelineStage.
     *
     * @throws \Exception
     */
    public function delete(
        PipelineStage $pipelineStage,
        int $deletedBy,
        ?User $actor = null
    ): bool {

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $pipelineStage,
            function (PipelineStage $pipelineStage) use ($actor, $deletedBy): void {
                $pipelineStage->deleted_by = $deletedBy;
                $pipelineStage->deleted_at = now();
                $pipelineStage->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_PIPELINE_STAGE,
                    $actor,
                    $pipelineStage,
                    ['before' => $this->auditLogService->snapshot($pipelineStage)],
                );
            });
    }

    /**
     * Force delete a pipelineStage (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        PipelineStage $pipelineStage,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $pipelineStage,
            function (PipelineStage $pipelineStage) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_PIPELINE_STAGE,
                    $actor,
                    $pipelineStage,
                    ['before' => $this->auditLogService->snapshot($pipelineStage)],
                );
            });
    }

    /**
     * Delete multiple pipelineStages.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $pipelineStageIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($pipelineStageIds, $deletedBy, &$count) {
            $pipelineStages = PipelineStage::whereIn('id', $pipelineStageIds)->get();

            foreach ($pipelineStages as $pipelineStage) {
                if ($this->delete($pipelineStage, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

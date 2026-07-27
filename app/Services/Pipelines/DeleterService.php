<?php

namespace App\Services\Pipelines;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\Pipeline;
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
     * Soft delete a pipeline.
     *
     * @throws \Exception
     */
    public function delete(
        Pipeline $pipeline,
        int $deletedBy,
        ?User $actor = null
    ): bool {

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $pipeline,
            function (Pipeline $pipeline) use ($actor, $deletedBy): void {
                $pipeline->deleted_by = $deletedBy;
                $pipeline->deleted_at = now();
                $pipeline->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_PIPELINE,
                    $actor,
                    $pipeline,
                    ['before' => $this->auditLogService->snapshot($pipeline)],
                );
            });
    }

    /**
     * Force delete a pipeline (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Pipeline $pipeline,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $pipeline,
            function (Pipeline $pipeline) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_PIPELINE,
                    $actor,
                    $pipeline,
                    ['before' => $this->auditLogService->snapshot($pipeline)],
                );
            });
    }

    /**
     * Delete multiple pipelines.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $pipelineIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($pipelineIds, $deletedBy, &$count) {
            $pipelines = Pipeline::whereIn('id', $pipelineIds)->get();

            foreach ($pipelines as $pipeline) {
                if ($this->delete($pipeline, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

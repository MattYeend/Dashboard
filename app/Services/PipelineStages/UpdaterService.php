<?php

namespace App\Services\PipelineStages;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\PipelineStage;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update an existing pipeline stage.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        PipelineStage $pipelineStage,
        array $data,
        int $updatedBy
    ): PipelineStage {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($pipelineStage);

        $pipelineStageData = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $pipelineStage,
            $pipelineStageData,
            function (PipelineStage $pipelineStage) use ($actor, $before, $updatedBy): void {
                $pipelineStage->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
                $fresh = $pipelineStage->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_PIPELINE_STAGE,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }
}

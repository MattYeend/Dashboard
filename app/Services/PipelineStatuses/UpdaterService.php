<?php

namespace App\Services\PipelineStatuses;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\PipelineStatus;
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
     * Update an existing pipeline status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        PipelineStatus $pipelineStatus,
        array $data,
        int $updatedBy
    ): PipelineStatus {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($pipelineStatus);

        $pipelineStatusData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        return $this->updateResource->handle(
            $pipelineStatus,
            $pipelineStatusData,
            function (PipelineStatus $pipelineStatus) use ($actor, $before): void {
                $fresh = $pipelineStatus->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_PIPELINE_STATUS,
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
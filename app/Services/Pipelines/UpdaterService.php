<?php

namespace App\Services\Pipelines;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Pipeline;
use App\Models\User;
use App\Notifications\PipelineStageAssignedNotification;
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
     * Update an existing pipeline.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Pipeline $pipeline,
        array $data,
        int $updatedBy
    ): Pipeline {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($pipeline);
        $previousAssignedTo = $pipeline->assigned_to;

        $pipelineData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        return $this->updateResource->handle(
            $pipeline,
            $pipelineData,
            function (Pipeline $pipeline) use ($actor, $before, $previousAssignedTo): void {
                $fresh = $pipeline->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_PIPELINE,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );

                if ($fresh->assigned_to !== $previousAssignedTo && $fresh->assigned_to) {
                    $fresh->assignee?->notify(new PipelineStageAssignedNotification($fresh));
                }
            });
    }
}

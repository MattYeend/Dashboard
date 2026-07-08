<?php

namespace App\Services\TaskStatuses;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\TaskStatus;
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
     * Update an existing task status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        TaskStatus $taskStatus,
        array $data,
        int $updatedBy
    ): TaskStatus {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($taskStatus);

        $taskStatusData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        return $this->updateResource->handle(
            $taskStatus,
            $taskStatusData,
            function (TaskStatus $taskStatus) use ($actor, $before): void {
                $fresh = $taskStatus->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_TASK_STATUS,
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

<?php

namespace App\Services\TaskStatuses;

use App\Models\Log;
use App\Models\TaskStatus;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService
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

        return DB::transaction(function () use ($taskStatus, $data, $actor, $updatedBy, $before) {
            $this->updateTaskStatus($taskStatus, $data, $updatedBy);

            $this->auditLogService->record(
                Log::ACTION_UPDATE_TASK_STATUS,
                $actor,
                $taskStatus,
                [
                    'before' => $before,
                    'after' => $this->auditLogService->snapshot($taskStatus),
                ],
            );

            return $taskStatus->fresh();
        });
    }

    /**
     * Update task status data.
     *
     * @param  array<string, mixed>  $data
     */
    protected function updateTaskStatus(TaskStatus $taskStatus, array $data, int $updatedBy): void
    {
        $taskStatusData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);
        $taskStatus->update($taskStatusData);
    }
}

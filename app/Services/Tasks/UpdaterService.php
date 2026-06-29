<?php

namespace App\Services\Tasks;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Task;
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
     * Update an existing task.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Task $task,
        array $data,
        int $updatedBy
    ): Task {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($task);

        $taskData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        return $this->updateResource->handle(
            $task,
            $taskData,
            function (Task $task) use ($actor, $before, $updatedBy): void {
                $fresh = $task->fresh();

                $task->updated_by = $updatedBy;
                $task->updated_at = now();
                $task->save();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_TASK,
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

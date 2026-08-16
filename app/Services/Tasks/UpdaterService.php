<?php

namespace App\Services\Tasks;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
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
        $previousAssignedTo = $task->assigned_to;

        $taskData = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $task,
            $taskData,
            function (Task $task) use ($actor, $before, $previousAssignedTo, $updatedBy): void {
                $task->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
                $fresh = $task->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_TASK,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );

                if ($fresh->assigned_to !== $previousAssignedTo && $fresh->assigned_to) {
                    $fresh->assignee?->notify(new TaskAssignedNotification($fresh));
                }
            });
    }
}

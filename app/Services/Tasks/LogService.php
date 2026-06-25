<?php

namespace App\Services\Tasks;

use App\Models\Log;
use App\Models\Task;
use App\Models\User;

class LogService
{
    /**
     * Log task creation.
     *
     * @return array<string, mixed>
     */
    public function logCreation(Task $task, User $actor, int $actorId): array
    {
        $data = $this->baseTaskData($task) + [
            'created_at' => now(),
            'created_by' => $actor->name,
        ];

        Log::log(Log::ACTION_CREATE_TASK, $data, $actorId);

        return $data;
    }

    /**
     * Log a task show event.
     *
     * @return array<string, mixed>
     */
    public function logShow(Task $task, User $actor, int $actorId): array
    {
        $data = $this->baseTaskData($task) + [
            'shown_at' => now(),
            'shown_by' => $actor->name,
        ];

        Log::log(Log::ACTION_VIEW_TASK, $data, $actorId);

        return $data;
    }

    /**
     * Log a task update event.
     *
     * @return array<string, mixed>
     */
    public function logUpdate(
        Task $task,
        User $actor,
        int $actorId,
        array $before
    ): array {
        $data = [
            'before' => $before,
            'after' => $this->baseTaskData($task),
            'updated_at' => now()->toDateTimeString(),
            'updated_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_UPDATE_TASK,
            $data,
            $actorId
        );

        return $data;
    }

    /**
     * Log a task deletion event.
     *
     * @return array<string, mixed>
     */
    public function logDeletion(Task $task, User $actor, int $actorId): array
    {
        $data = $this->baseTaskData($task) + [
            'deleted_at' => now(),
            'deleted_by' => $actor->name,
        ];

        Log::log(Log::ACTION_DELETE_TASK, $data, $actorId);

        return $data;
    }

    /**
     * Log task force deletion (permanent).
     *
     * @return array<string, mixed>
     */
    public function logForceDeletion(Task $task, User $actor, int $actorId): array
    {
        $data = $this->baseTaskData($task) + [
            'force_deleted_at' => now(),
            'force_deleted_by' => $actor->name,
        ];

        Log::log(Log::ACTION_FORCE_DELETE_TASK, $data, $actorId);

        return $data;
    }

    /**
     * Log a task restoration event.
     *
     * @return array<string, mixed>
     */
    public function logRestoration(Task $task, User $actor, int $actorId): array
    {
        $data = $this->baseTaskData($task) + [
            'restored_at' => now(),
            'restored_by' => $actor->name,
        ];

        Log::log(Log::ACTION_RESTORE_TASK, $data, $actorId);

        return $data;
    }

    /**
     * Log a task import event.
     *
     * @param  array<int, mixed>  $importData
     * @return array<string, mixed>
     */
    public function logImport(array $importData, User $actor, int $actorId): array
    {
        $data = [
            'imported_at' => now(),
            'imported_by' => $actor->name,
            'imported_count' => count($importData),
            'imported_data_sample' => array_slice($importData, 0, 5),
        ];

        Log::log(Log::ACTION_IMPORT_TASK, $data, $actorId);

        return $data;
    }

    /**
     * Log a task export event.
     *
     * @param  array<int, mixed>  $exportData
     * @return array<string, mixed>
     */
    public function logExport(array $exportData, User $actor, int $actorId): array
    {
        $data = [
            'exported_at' => now(),
            'exported_by' => $actor->name,
            'exported_count' => count($exportData),
            'exported_data_sample' => array_slice($exportData, 0, 5),
        ];

        Log::log(Log::ACTION_EXPORT_TASK, $data, $actorId);

        return $data;
    }

    /**
     * Log a task update performed by a scheduled task (cron).
     *
     * @return array<string, mixed>
     */
    public function logUpdateByCron(Task $task): array
    {
        $data = $this->baseTaskData($task) + [
            'updated_at' => now(),
            'updated_by' => 'System (Cron)',
        ];

        Log::log(Log::ACTION_TASK_UPDATED_BY_CRON, $data, null);

        return $data;
    }

    /**
     * Capture a snapshot of the task's current state for before/after logging.
     *
     * @return array<string, mixed>
     */
    public function captureSnapshot(Task $task): array
    {
        return $this->baseTaskData($task);
    }

    /**
     * Get base task data for logging.
     *
     * @return array<string, mixed>
     */
    protected function baseTaskData(Task $task): array
    {
        return $this->getTaskData($task);
    }

    /**
     * Get task data for logging.
     *
     * @return array<string, mixed>
     */
    protected function getTaskData(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'due_date' => $task->due_date,
            'assigned_date' => $task->assigned_date,
            'assigned_to' => $task->assigned_to,
            'status_id' => $task->status_id,
            'meta' => $task->meta,
        ];
    }
}

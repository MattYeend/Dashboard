<?php

namespace App\Services\TaskStatuses;

use App\Models\Log;
use App\Models\TaskStatus;
use App\Models\User;

class LogService
{
    /**
     * Log task status creation.
     *
     * @return array<string, mixed>
     */
    public function logCreation(
        TaskStatus $taskStatus,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseTaskStatusData($taskStatus) + [
            'created_at' => now(),
            'created_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_CREATE_TASK_STATUS,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a task status show event.
     *
     * @return array<string, mixed>
     */
    public function logShow(
        TaskStatus $taskStatus,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseTaskStatusData($taskStatus) + [
            'shown_at' => now(),
            'shown_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_VIEW_TASK_STATUS,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a task status update event.
     *
     * @return array<string, mixed>
     */
    public function logUpdate(
        TaskStatus $taskStatus,
        User $actor,
        int $actorId,
        array $before
    ): array {
        $data = [
            'before' => $before,
            'after' => $this->baseTaskStatusData($taskStatus),
            'updated_at' => now()->toDateTimeString(),
            'updated_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_UPDATE_TASK_STATUS,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a task status deletion event.
     *
     * @return array<string, mixed>
     */
    public function logDeletion(
        TaskStatus $taskStatus,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseTaskStatusData($taskStatus) + [
            'deleted_at' => now(),
            'deleted_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_DELETE_TASK_STATUS,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log task status force deletion (permanent).
     *
     * @return array<string, mixed>
     */
    public function logForceDeletion(
        TaskStatus $taskStatus,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseTaskStatusData($taskStatus) + [
            'force_deleted_at' => now(),
            'force_deleted_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_FORCE_DELETE_TASK_STATUS,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a task status restoration event.
     *
     * @return array<string, mixed>
     */
    public function logRestoration(
        TaskStatus $taskStatus,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseTaskStatusData($taskStatus) + [
            'restored_at' => now(),
            'restored_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_RESTORE_TASK_STATUS,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a task status import event.
     *
     * @param  array<int, mixed>  $importData
     * @return array<string, mixed>
     */
    public function logImport(
        array $importData,
        User $actor,
        int $actorId
    ): array {
        $data = [
            'imported_at' => now(),
            'imported_by' => $actor->name,
            'imported_count' => count($importData),
            'imported_data_sample' => array_slice($importData, 0, 5),
        ];

        Log::log(
            Log::ACTION_IMPORT_TASK_STATUS,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a task status export event.
     *
     * @param  array<int, mixed>  $exportData
     * @return array<string, mixed>
     */
    public function logExport(
        array $exportData,
        User $actor,
        int $actorId
    ): array {
        $data = [
            'exported_at' => now(),
            'exported_by' => $actor->name,
            'exported_count' => count($exportData),
            'exported_data_sample' => array_slice($exportData, 0, 5),
        ];

        Log::log(
            Log::ACTION_EXPORT_TASK_STATUS,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a task status update event performed by a scheduled task (cron).
     *
     * @return array<string, mixed>
     */
    public function logUpdateByCron(TaskStatus $taskStatus): array
    {
        $data = $this->baseTaskStatusData($taskStatus) + [
            'updated_at' => now(),
            'updated_by' => 'System (Cron)',
        ];

        Log::log(
            Log::ACTION_TASK_STATUS_UPDATED_BY_CRON,
            $data,
            null,
        );

        return $data;
    }

    /**
     * Capture a snapshot of the task status' current state for before/after logging.
     *
     * @return array<string, mixed>
     */
    public function captureSnapshot(TaskStatus $taskStatus): array
    {
        return $this->baseTaskStatusData($taskStatus);
    }

    /**
     * Get base task status data for logging.
     *
     * @return array<string, mixed>
     */
    protected function baseTaskStatusData(TaskStatus $taskStatus): array
    {
        return $this->getTaskStatusData($taskStatus);
    }

    /**
     * Get task status data for logging.
     *
     * @return array<string, mixed>
     */
    protected function getTaskStatusData(TaskStatus $taskStatus): array
    {
        return [
            'id' => $taskStatus->id,
            'title' => $taskStatus->title,
            'description' => $taskStatus->description,
            'background_colour' => $taskStatus->background_colour,
            'text_colour' => $taskStatus->text_colour,
            'meta' => $taskStatus->meta,
        ];
    }
}

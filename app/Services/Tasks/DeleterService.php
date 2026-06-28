<?php

namespace App\Services\Tasks;

use App\Models\Log;
use App\Models\Task;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService
    ) {}

    /**
     * Soft delete a task.
     *
     * @throws \Exception
     */
    public function delete(Task $task, int $deletedBy): bool
    {
        $actor = User::findOrFail($deletedBy);

        return DB::transaction(function () use ($task, $deletedBy, $actor) {
            $this->auditLogService->record(
                Log::ACTION_DELETE_TASK,
                $actor,
                $task,
                ['before' => $task->toArray()],
            );

            $task->deleted_by = $deletedBy;
            $task->save();

            return $task->delete();
        });
    }

    /**
     * Force delete a task (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(Task $task, int $deletedBy): bool
    {
        $actor = User::findOrFail($deletedBy);

        return DB::transaction(function () use ($task, $actor) {

            $this->auditLogService->record(
                Log::ACTION_FORCE_DELETE_TASK,
                $actor,
                $task,
                ['before' => $task->toArray()],
            );

            return $task->forceDelete();
        });
    }

    /**
     * Delete multiple tasks.
     *
     * @throws \Exception
     */
    public function deleteMultiple(array $taskIds, int $deletedBy): int
    {
        $count = 0;

        DB::transaction(function () use ($taskIds, $deletedBy, &$count) {
            $tasks = Task::whereIn('id', $taskIds)->get();

            foreach ($tasks as $task) {
                if ($this->delete($task, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

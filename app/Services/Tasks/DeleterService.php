<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected LogService $logService
    ) {}

    /**
     * Soft delete a task.
     *
     * @throws \Exception
     */
    public function delete(Task $task, ?int $deletedBy = null): bool
    {
        return DB::transaction(function () use ($task, $deletedBy) {
            $actor = User::findOrFail($deletedBy);
            $task->deleted_by = $deletedBy;
            $task->save();

            $result = $task->delete();

            $this->logService->logDeletion($task, $actor, $deletedBy);

            return $result;
        });
    }

    /**
     * Force delete a task (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(Task $task, ?int $deletedBy = null): bool
    {
        return DB::transaction(function () use ($task, $deletedBy) {
            $actor = User::findOrFail($deletedBy);
            $this->logService->logForceDeletion($task, $actor, $deletedBy);

            return $task->forceDelete();
        });
    }

    /**
     * Delete multiple tasks.
     *
     * @throws \Exception
     */
    public function deleteMultiple(array $taskIds, ?int $deletedBy = null): int
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

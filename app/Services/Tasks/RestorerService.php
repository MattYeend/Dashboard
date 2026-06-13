<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly LogService $logService
    ) {}

    /**
     * Restore a soft-deleted task.
     *
     * @throws \Exception
     */
    public function restore(Task $task, int $restoredBy): Task
    {
        $actor = User::findOrFail($restoredBy);

        return DB::transaction(function () use ($task, $restoredBy, $actor) {
            $task->restored_by = $restoredBy;
            $task->restored_at = now();
            $task->save();

            $task->restore();

            $this->logService->logRestoration($task, $actor, $restoredBy);

            return $task->fresh();
        });
    }

    /**
     * Restore multiple soft-deleted tasks.
     *
     * @return int Number of tasks restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(array $taskIds, int $restoredBy): int
    {
        $count = 0;

        DB::transaction(function () use ($taskIds, $restoredBy, &$count) {
            /** @var Collection<int, Task> $tasks */
            $tasks = Task::withTrashed()
                ->whereIn('id', $taskIds)
                ->get();

            foreach ($tasks as $task) {
                if ($task->trashed()) {
                    $this->restore($task, $restoredBy);
                    $count++;
                }
            }
        });

        return $count;
    }
}

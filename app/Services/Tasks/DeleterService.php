<?php

namespace App\Services\Tasks;

use App\Actions\DeleteResource;
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
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete a task.
     *
     * @throws \Exception
     */
    public function delete(Task $task, int $deletedBy, ?User $actor = null): bool
    {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $task,
            function (Task $task) use ($actor, $deletedBy): void {
                $task->deleted_by = $deletedBy;
                $task->deleted_at = now();
                $task->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_TASK,
                    $actor,
                    $task,
                    ['before' => $this->auditLogService->snapshot($task)],
                );
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

        return $this->deleteResource->forceHandle(
            $task,
            function (Task $task) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_TASK,
                    $actor,
                    $task,
                    ['before' => $this->auditLogService->snapshot($task)],
                );
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
            $actor = User::findOrFail($deletedBy);
            $tasks = Task::whereIn('id', $taskIds)->get();

            foreach ($tasks as $task) {
                if ($this->delete($task, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

<?php

namespace App\Services\Tasks;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Models\User;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $destructor,
        protected readonly RestorerService $restorer
    ) {}

    /**
     * Create a new task.
     */
    public function store(StoreTaskRequest $request): Task
    {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing task.
     */
    public function update(UpdateTaskRequest $request, Task $task): Task
    {
        return $this->updater->update(
            $task,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a task.
     */
    public function destroy(Task $task): void
    {
        $this->destructor->delete($task, auth()->id());
    }

    /**
     * Restore a soft-deleted task.
     */
    public function restore(int $id): Task
    {
        $task = Task::withTrashed()->findOrFail($id);

        return $this->restorer->restore($task, auth()->id());
    }

    /**
     * Force delete a task, permanently removing it from the database.
     */
    public function forceDelete(int $id): void
    {
        $task = Task::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($task, auth()->id());
    }

    /**
     * Bulk restore tasks.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $tasks = Task::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($tasks as $task) {
            $authoriseCallback($task);
            $this->restorer->restore($task, $actor->id);
            $restored[] = $task->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($tasks->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete tasks.
     */
    public function bulkDelete(array $ids, User $actor, callable $authoriseCallback): array
    {
        $deleted = [];

        foreach ($ids as $id) {
            $task = Task::findOrFail($id);
            $authoriseCallback($task);

            $this->destructor->delete($task, $actor->id);
            $deleted[] = $id;
        }

        return $deleted;
    }
}

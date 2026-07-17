<?php

namespace App\Services\TaskStatuses;

use App\Http\Requests\TaskStatuses\StoreTaskStatusRequest;
use App\Http\Requests\TaskStatuses\UpdateTaskStatusRequest;
use App\Models\TaskStatus;
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
        protected readonly RestorerService $restorer,
    ) {}

    /**
     * Create a new task status.
     */
    public function store(
        StoreTaskStatusRequest $request
    ): TaskStatus {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing task status.
     */
    public function update(
        UpdateTaskStatusRequest $request,
        TaskStatus $taskStatus
    ): TaskStatus {
        return $this->updater->update(
            $taskStatus,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a task status.
     */
    public function destroy(
        TaskStatus $taskStatus,
        User $actor
    ): void {
        $this->destructor->delete($taskStatus, $actor->id);
    }

    /**
     * Restore a soft-deleted task status.
     */
    public function restore(
        int $id,
        User $actor
    ): TaskStatus {
        $taskStatus = TaskStatus::withTrashed()->findOrFail($id);

        return $this->restorer->restore($taskStatus, $actor->id);
    }

    /**
     * Force delete a task status, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $taskStatus = TaskStatus::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($taskStatus, $actor->id);
    }

    /**
     * Bulk restore task statuses.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $taskStatuses = TaskStatus::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($taskStatuses as $taskStatus) {
            /** @var TaskStatus $taskStatus */
            $authoriseCallback($taskStatus);
            $this->restorer->restore($taskStatus, $actor->id);
            $restored[] = $taskStatus->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($taskStatuses->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete task statuses.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $deleted = [];

        foreach ($ids as $id) {
            $taskStatus = TaskStatus::findOrFail($id);
            $authoriseCallback($taskStatus);

            $this->destructor->delete($taskStatus, $actor->id);
            $deleted[] = $id;
        }

        return $deleted;
    }
}

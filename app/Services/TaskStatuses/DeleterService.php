<?php

namespace App\Services\TaskStatuses;

use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly LogService $logService
    ) {}

    /**
     * Soft delete a taskStatus.
     *
     * @throws \Exception
     */
    public function delete(
        TaskStatus $taskStatus,
        int $deletedBy
    ): bool {
        
        $actor = User::findOrFail($deletedBy);
        return DB::transaction(function () use ($taskStatus, $deletedBy, $actor) {
            $taskStatus->deleted_by = $deletedBy;
            $taskStatus->save();

            $result = $taskStatus->delete();

            $this->logService->logDeletion($taskStatus, $actor, $deletedBy);

            return $result;
        });
    }

    /**
     * Force delete a taskStatus (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        TaskStatus $taskStatus,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);
        return DB::transaction(function () use ($taskStatus, $deletedBy, $actor) {
            $this->logService->logForceDeletion($taskStatus, $actor, $deletedBy);

            return $taskStatus->forceDelete();
        });
    }

    /**
     * Delete multiple taskStatuses.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $taskStatusIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($taskStatusIds, $deletedBy, &$count) {
            $taskStatuses = TaskStatus::whereIn('id', $taskStatusIds)->get();

            foreach ($taskStatuses as $taskStatus) {
                if ($this->delete($taskStatus, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

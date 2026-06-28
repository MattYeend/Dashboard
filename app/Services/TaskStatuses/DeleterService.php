<?php

namespace App\Services\TaskStatuses;

use App\Models\Log;
use App\Models\TaskStatus;
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
            $this->auditLogService->record(
                Log::ACTION_DELETE_TASK_STATUS,
                $actor,
                $taskStatus,
                ['before' => $taskStatus->toArray()],
            );

            $taskStatus->deleted_by = $deletedBy;
            $taskStatus->save();

            return $taskStatus->delete();

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

        return DB::transaction(function () use ($taskStatus, $actor) {
            $this->auditLogService->record(
                Log::ACTION_FORCE_DELETE_TASK_STATUS,
                $actor,
                $taskStatus,
                ['before' => $taskStatus->toArray()],
            );

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

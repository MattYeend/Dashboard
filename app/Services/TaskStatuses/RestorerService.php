<?php

namespace App\Services\TaskStatuses;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\TaskStatus;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the resorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted taskStatus.
     *
     * @throws \Exception
     */
    public function restore(
        TaskStatus $taskStatus,
        int $restoredBy
    ): TaskStatus {
        $actor = User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $taskStatus,
            function (TaskStatus $taskStatus) use ($actor, $restoredBy): void {
                $taskStatus->restored_by = $restoredBy;
                $taskStatus->restored_at = now();
                $taskStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_TASK_STATUS,
                    $actor,
                    $taskStatus,
                    ['before' => $taskStatus->toArray()],
                );
            });
    }

    /**
     * Restore multiple soft-deleted taskStatuses.
     *
     * @return int Number of taskStatuses restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $taskStatusIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($taskStatusIds, $restoredBy, &$count) {
            /** @var Collection<int,TaskStatus> $taskStatuses */
            $taskStatuses = TaskStatus::withTrashed()
                ->whereIn('id', $taskStatusIds)
                ->get();

            foreach ($taskStatuses as $taskStatus) {
                if ($taskStatus->trashed()) {
                    $this->restore($taskStatus, $restoredBy);
                    $count++;
                }
            }
        });

        return $count;
    }
}

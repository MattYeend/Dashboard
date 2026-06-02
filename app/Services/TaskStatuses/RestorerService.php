<?php

namespace App\Services\TaskStatuses;

use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the resorer service.
     */
    public function __construct(
        protected LogService $logService
    ) {}

    /**
     * Restore a soft-deleted taskStatus.
     *
     * @throws \Exception
     */
    public function restore(
        TaskStatus $taskStatus,
        ?int $restoredBy = null
    ): TaskStatus {
        return DB::transaction(function () use ($taskStatus, $restoredBy) {
            $actor = User::findOrFail($restoredBy);

            $taskStatus->restored_by = $restoredBy;
            $taskStatus->restored_at = now();
            $taskStatus->save();

            $taskStatus->restore();

            $this->logService->logRestoration($taskStatus, $actor, $restoredBy);

            return $taskStatus->fresh();
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
        ?int $restoredBy = null
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

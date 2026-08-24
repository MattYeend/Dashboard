<?php

namespace App\Services\Activities;

use App\Actions\RestoreResource;
use App\Models\Activity;
use App\Models\Log;
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
     * Restore a soft-deleted activity.
     *
     * @throws \Exception
     */
    public function restore(
        Activity $activity, 
        int $restoredBy, 
        ?User $actor = null
    ): Activity {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $activity,
            function (Activity $activity) use ($actor, $restoredBy): void {
                $activity->restored_by = $restoredBy;
                $activity->restored_at = now();
                $activity->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_ACTIVITY,
                    $actor,
                    $activity,
                    ['before' => $this->auditLogService->snapshot($activity)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted activities.
     *
     * @return int Number of activities restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $activityIds, 
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($activityIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,Activity> $activities */
            $activities = Activity::withTrashed()->whereIn('id', $activityIds)->get();

            foreach ($activities as $activity) {
                if ($activity->trashed()) {
                    $this->restore($activity, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}

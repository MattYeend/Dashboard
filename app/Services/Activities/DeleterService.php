<?php

namespace App\Services\Activities;

use App\Actions\DeleteResource;
use App\Models\Activity;
use App\Models\Log;
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
     * Soft delete a activity.
     *
     * @throws \Exception
     */
    public function delete(
        Activity $activity,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $activity,
            function (Activity $activity) use ($actor, $deletedBy): void {
                $activity->deleted_by = $deletedBy;
                $activity->deleted_at = now();
                $activity->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_ACTIVITY,
                    $actor,
                    $activity,
                    ['before' => $this->auditLogService->snapshot($activity)],
                );
            });
    }

    /**
     * Force delete a activity (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Activity $activity,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $activity,
            function (Activity $activity) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_ACTIVITY,
                    $actor,
                    $activity,
                    ['before' => $this->auditLogService->snapshot($activity)],
                );
            });
    }

    /**
     * Delete multiple activities.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $activityIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($activityIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $activities = Activity::whereIn('id', $activityIds)->get();

            foreach ($activities as $activity) {
                if ($this->delete($activity, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

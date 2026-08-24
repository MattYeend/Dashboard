<?php

namespace App\Services\Activities;

use App\Actions\UpdateResource;
use App\Models\Activity;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update an existing activity.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(Activity $activity, array $data, int $updatedBy): Activity
    {
        $actor = User::findOrFail($updatedBy);
        $before = $this->auditLogService->snapshot($activity);

        $activityData = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $activity,
            $activityData,
            function (Activity $activity) use ($actor, $before, $updatedBy): void {
                $activity->forceFill(['updated_by' => $updatedBy])->save();
                $fresh = $activity->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_ACTIVITY,
                    $actor,
                    $fresh,
                    ['before' => $before, 'after' => $this->auditLogService->snapshot($fresh)],
                );
            });
    }
}

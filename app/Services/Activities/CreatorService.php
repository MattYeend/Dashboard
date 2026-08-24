<?php

namespace App\Services\Activities;

use App\Actions\CreateResource;
use App\Models\Activity;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Create a new activity.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Activity
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Activity {
                $activityData = $this->dataPreparation->prepareForCreation(
                    $data,
                    $data['activityable_type'],
                    $data['activityable_id'],
                );

                $activity = Activity::create($activityData);

                $activity->forceFill(['created_by' => $createdBy])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_ACTIVITY,
                    $actor,
                    $activity,
                    ['after' => $this->auditLogService->snapshot($activity)],
                );

                return $activity;
            });
    }
}

<?php

namespace App\Services\NotificationBroadcasts;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\NotificationBroadcast;
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
     * Create a new notification broadcast.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): NotificationBroadcast
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): NotificationBroadcast {
                $broadcastData = $this->dataPreparation->prepareForCreation($data);

                $newNotificationBroadcast = NotificationBroadcast::create($broadcastData);

                $newNotificationBroadcast->forceFill([
                    'created_by' => $createdBy,
                    'updated_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_NOTIFICATION_BROADCAST,
                    $actor,
                    $newNotificationBroadcast,
                    ['after' => $this->auditLogService->snapshot($newNotificationBroadcast)],
                );

                return $newNotificationBroadcast;
            });
    }
}

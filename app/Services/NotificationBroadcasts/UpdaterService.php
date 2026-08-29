<?php

namespace App\Services\NotificationBroadcasts;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\NotificationBroadcast;
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
     * Update an existing notification broadcast.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        NotificationBroadcast $notificationBroadcast,
        array $data,
        int $updatedBy
    ): NotificationBroadcast {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($notificationBroadcast);

        $broadcastData = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $notificationBroadcast,
            $broadcastData,
            function (NotificationBroadcast $notificationBroadcast) use ($actor, $before, $updatedBy): void {
                $notificationBroadcast->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
                $fresh = $notificationBroadcast->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_NOTIFICATION_BROADCAST,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }
}

<?php

namespace App\Services\NotificationBroadcasts;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\NotificationBroadcast;
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
     * Soft delete a notification broadcast.
     *
     * @throws \Exception
     */
    public function delete(NotificationBroadcast $notificationBroadcast, int $deletedBy, ?User $actor = null): bool
    {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $notificationBroadcast,
            function (NotificationBroadcast $notificationBroadcast) use ($actor, $deletedBy): void {
                $notificationBroadcast->deleted_by = $deletedBy;
                $notificationBroadcast->deleted_at = now();
                $notificationBroadcast->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_NOTIFICATION_BROADCAST,
                    $actor,
                    $notificationBroadcast,
                    ['before' => $this->auditLogService->snapshot($notificationBroadcast)],
                );
            });
    }

    /**
     * Force delete a notification broadcast (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(NotificationBroadcast $notificationBroadcast, int $deletedBy): bool
    {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $notificationBroadcast,
            function (NotificationBroadcast $notificationBroadcast) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_NOTIFICATION_BROADCAST,
                    $actor,
                    $notificationBroadcast,
                    ['before' => $this->auditLogService->snapshot($notificationBroadcast)],
                );
            });
    }

    /**
     * Delete multiple notification broadcasts.
     *
     * @throws \Exception
     */
    public function deleteMultiple(array $notificationBroadcastIds, int $deletedBy): int
    {
        $count = 0;

        DB::transaction(function () use ($notificationBroadcastIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $notificationBroadcasts = NotificationBroadcast::whereIn('id', $notificationBroadcastIds)->get();

            foreach ($notificationBroadcasts as $notificationBroadcast) {
                if ($this->delete($notificationBroadcast, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

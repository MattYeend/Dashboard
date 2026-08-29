<?php

namespace App\Services\NotificationBroadcasts;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\NotificationBroadcast;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted notification broadcast.
     *
     * @throws \Exception
     */
    public function restore(
        NotificationBroadcast $notificationBroadcast,
        int $restoredBy,
        ?User $actor = null
    ): NotificationBroadcast {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $notificationBroadcast,
            function (NotificationBroadcast $notificationBroadcast) use ($actor, $restoredBy): void {
                $notificationBroadcast->restored_by = $restoredBy;
                $notificationBroadcast->restored_at = now();
                $notificationBroadcast->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_NOTIFICATION_BROADCAST,
                    $actor,
                    $notificationBroadcast,
                    // Snapshot is taken after the restore fields are saved above.
                    ['after' => $this->auditLogService->snapshot($notificationBroadcast)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted notification broadcasts.
     *
     * @return int Number of notification broadcasts restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(array $notificationBroadcastIds, int $restoredBy): int
    {
        $count = 0;

        DB::transaction(function () use ($notificationBroadcastIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, NotificationBroadcast> $notificationBroadcasts */
            $notificationBroadcasts = NotificationBroadcast::withTrashed()
                ->whereIn('id', $notificationBroadcastIds)
                ->get();

            foreach ($notificationBroadcasts as $notificationBroadcast) {
                if ($notificationBroadcast->trashed()) {
                    $this->restore($notificationBroadcast, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}

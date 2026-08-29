<?php

namespace App\Actions;

use App\Models\Log;
use App\Models\NotificationBroadcast;
use App\Models\User;
use App\Notifications\AdminBroadcastNotification;
use App\Services\AuditLogService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

/**
 * Resolves the target audience for a NotificationBroadcast, dispatches it as
 * a real (queued) Laravel notification to each recipient, and stamps the
 * broadcast as sent.
 *
 * Kept as its own action, separate from the management service, since
 * "resolve audience + dispatch + stamp + audit" is a distinct unit of work
 * from the plain CRUD operations.
 */
class SendNotificationBroadcast
{
    /**
     * Inject the required services into the action.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Send the notification broadcast to its configured audience.
     *
     * @throws RuntimeException
     */
    public function handle(NotificationBroadcast $notificationBroadcast, int $actorId): NotificationBroadcast
    {
        if ($notificationBroadcast->sent_at !== null) {
            throw new RuntimeException('This notification has already been sent.');
        }

        $actor = User::findOrFail($actorId);
        $before = $this->auditLogService->snapshot($notificationBroadcast);

        return DB::transaction(function () use ($notificationBroadcast, $actor, $before): NotificationBroadcast {
            $recipients = $this->resolveRecipients($notificationBroadcast);

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new AdminBroadcastNotification($notificationBroadcast));
            }

            $notificationBroadcast->sent_at = now();
            $notificationBroadcast->sent_by = $actor->id;
            $notificationBroadcast->updated_by = $actor->id;
            $notificationBroadcast->save();

            $this->auditLogService->record(
                Log::ACTION_SEND_NOTIFICATION_BROADCAST,
                $actor,
                $notificationBroadcast,
                [
                    'before' => $before,
                    'after' => $this->auditLogService->snapshot($notificationBroadcast),
                ],
            );

            return $notificationBroadcast;
        });
    }

    /**
     * Resolve the actual recipient collection for the broadcast's audience type.
     *
     * @return Collection<int, User>
     */
    private function resolveRecipients(NotificationBroadcast $notificationBroadcast): Collection
    {
        return match ($notificationBroadcast->audience_type) {
            // Spatie's role() scope — never interpolate raw role names into SQL.
            'role' => User::role($notificationBroadcast->audience_ids ?? [])->get(),
            'users' => User::query()->whereIn('id', $notificationBroadcast->audience_ids ?? [])->get(),
            default => User::query()->get(),
        };
    }
}

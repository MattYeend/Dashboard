<?php

declare(strict_types=1);

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
     * The returned instance is the locked, freshly loaded row, so callers
     * should use it rather than the instance they passed in.
     *
     * @throws RuntimeException
     */
    public function handle(NotificationBroadcast $notificationBroadcast, int $actorId): NotificationBroadcast
    {
        $actor = User::findOrFail($actorId);

        return DB::transaction(function () use ($notificationBroadcast, $actor): NotificationBroadcast {
            $locked = NotificationBroadcast::query()
                ->lockForUpdate()
                ->findOrFail($notificationBroadcast->getKey());

            if ($locked->sent_at !== null) {
                throw new RuntimeException('This notification has already been sent.');
            }

            $before = $this->auditLogService->snapshot($locked);
            $recipients = $this->resolveRecipients($locked);

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new AdminBroadcastNotification($locked));
            }

            $locked->sent_at = now();
            $locked->sent_by = $actor->id;
            $locked->updated_by = $actor->id;
            $locked->save();

            $this->auditLogService->record(
                Log::ACTION_SEND_NOTIFICATION_BROADCAST,
                $actor,
                $locked,
                [
                    'before' => $before,
                    'after' => $this->auditLogService->snapshot($locked),
                ],
            );

            return $locked;
        });
    }

    /**
     * Resolve the actual recipient collection for the broadcast's audience type.
     *
     * @return Collection<int, User>
     *
     * @throws RuntimeException
     */
    private function resolveRecipients(NotificationBroadcast $notificationBroadcast): Collection
    {
        return match ($notificationBroadcast->audience_type) {
            // Spatie's role() scope - never interpolate raw role names into SQL.
            NotificationBroadcast::AUDIENCE_ROLE => User::role($notificationBroadcast->audience_ids ?? [])->get(),
            NotificationBroadcast::AUDIENCE_USERS => User::query()->whereIn('id', $notificationBroadcast->audience_ids ?? [])->get(),
            NotificationBroadcast::AUDIENCE_ALL => User::query()->get(),
            default => throw new RuntimeException('Unsupported audience type.'),
        };
    }
}

<?php

namespace App\Notifications;

use App\Models\NotificationBroadcast;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * The actual Laravel notification delivered to each recipient when a
 * NotificationBroadcast is sent. Database-channel only, so it surfaces
 * through the existing polling inbox rather than sending email/SMS.
 */
class AdminBroadcastNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Inject the broadcast being delivered.
     */
    public function __construct(
        private readonly NotificationBroadcast $broadcast,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation stored in the database channel.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'notification_broadcast_id' => $this->broadcast->id,
            'title' => $this->broadcast->title,
            'body' => $this->broadcast->body,
        ];
    }
}

<?php

namespace App\Services\Notifications;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdaterService
{
    /**
     * Mark a single notification as read.
     *
     * Looks the notification up through the notifiable's own relation,
     * so a user can never mark (or discover the existence of) another
     * user's notification by guessing an ID.
     *
     * @throws ModelNotFoundException
     */
    public function markAsRead(User $notifiable, string $id): void
    {
        $notification = $notifiable->notifications()->findOrFail($id);

        $notification->markAsRead();
    }

    /**
     * Mark all unread notifications as read for the given user.
     */
    public function markAllAsRead(User $notifiable): void
    {
        $notifiable->unreadNotifications->markAsRead();
    }
}

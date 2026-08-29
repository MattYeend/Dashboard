<?php

namespace App\Services\NotificationBroadcasts;

use App\Models\NotificationBroadcast;

class FormatterService
{
    /**
     * Format a single notification broadcast with all data.
     *
     * @return array<string, mixed>
     */
    public function format(NotificationBroadcast $notificationBroadcast): array
    {
        return [
            'id' => $notificationBroadcast->id,
            'title' => $notificationBroadcast->title,
            'body' => $notificationBroadcast->body,
            'audience_type' => $notificationBroadcast->audience_type,
            'audience_ids' => $notificationBroadcast->audience_ids,
            'sent_at' => $notificationBroadcast->sent_at,
            'meta' => $notificationBroadcast->meta,
            'created_at' => $notificationBroadcast->created_at,
            'updated_at' => $notificationBroadcast->updated_at,
            'deleted_at' => $notificationBroadcast->deleted_at,
            'restored_at' => $notificationBroadcast->restored_at,
            'creator' => $notificationBroadcast->creator ? ['id' => $notificationBroadcast->creator->id, 'name' => $notificationBroadcast->creator->name] : null,
            'updater' => $notificationBroadcast->updater ? ['id' => $notificationBroadcast->updater->id, 'name' => $notificationBroadcast->updater->name] : null,
            'deleter' => $notificationBroadcast->deleter ? ['id' => $notificationBroadcast->deleter->id, 'name' => $notificationBroadcast->deleter->name] : null,
            'restorer' => $notificationBroadcast->restorer ? ['id' => $notificationBroadcast->restorer->id, 'name' => $notificationBroadcast->restorer->name] : null,
            'sender' => $notificationBroadcast->sender ? ['id' => $notificationBroadcast->sender->id, 'name' => $notificationBroadcast->sender->name] : null,
        ];
    }
}

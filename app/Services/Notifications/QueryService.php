<?php

namespace App\Services\Notifications;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class QueryService
{
    /**
     * Get a paginated list of all notifications for the given user.
     */
    public function paginated(User $notifiable, int $perPage = 15): LengthAwarePaginator
    {
        return $notifiable->notifications()->paginate($perPage);
    }

    /**
     * Get all unread notifications for the given user.
     */
    public function unread(User $notifiable): Collection
    {
        return $notifiable->unreadNotifications;
    }
}

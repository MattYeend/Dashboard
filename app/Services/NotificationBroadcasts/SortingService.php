<?php

namespace App\Services\NotificationBroadcasts;

use App\Models\NotificationBroadcast;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query.
     *
     * @param  Builder<NotificationBroadcast>  $query
     * @return Builder<NotificationBroadcast>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'desc'
    ): Builder {
        $sortBy = $sortBy ?? 'created_at';
        $sortDirection = strtolower($sortDirection ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'title' => $query->orderBy('title', $sortDirection),
            'audience_type' => $query->orderBy('audience_type', $sortDirection),
            'sent_at' => $query->orderBy('sent_at', $sortDirection),
            default => $query->orderBy('created_at', $sortDirection),
        };
    }

    /**
     * Get available sort fields.
     *
     * @return array<string, string>
     */
    public function getAvailableSortFields(): array
    {
        return [
            'title' => 'Title',
            'audience_type' => 'Audience',
            'sent_at' => 'Sent Date',
            'created_at' => 'Created Date',
        ];
    }
}

<?php

namespace App\Services\NotificationBroadcasts;

use App\Models\NotificationBroadcast;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<NotificationBroadcast>  $query
     * @return Builder<NotificationBroadcast>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%");
        });
    }

    /**
     * Apply an audience type filter to the query.
     *
     * @param  Builder<NotificationBroadcast>  $query
     * @return Builder<NotificationBroadcast>
     */
    public function applyAudienceTypeFilter(Builder $query, ?string $audienceType): Builder
    {
        if ($audienceType === null) {
            return $query;
        }

        return $query->where('audience_type', $audienceType);
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<NotificationBroadcast>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<NotificationBroadcast>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyAudienceTypeFilter($query, $filters['audience_type'] ?? null);

        return $query;
    }
}

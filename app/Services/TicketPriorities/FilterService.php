<?php

namespace App\Services\TicketPriorities;

use App\Models\TicketPriority;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<TicketPriority>  $query
     * @return Builder<TicketPriority>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where('title', 'like', "%{$search}%");
    }

    /**
     * Apply a level filter to the query.
     *
     * @param  Builder<TicketPriority>  $query
     * @return Builder<TicketPriority>
     */
    public function applyLevel(Builder $query, ?int $level): Builder
    {
        if ($level === null) {
            return $query;
        }

        return $query->where('level', $level);
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<TicketPriority>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<TicketPriority>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);

        return $this->applyLevel($query, $filters['level'] ?? null);
    }
}

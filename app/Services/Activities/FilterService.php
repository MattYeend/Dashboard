<?php

namespace App\Services\Activities;

use App\Models\Activity;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * @param  Builder<Activity>  $query
     * @return Builder<Activity>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where('description', 'like', "%{$search}%");
    }

    /**
     * @param  Builder<Activity>  $query
     * @return Builder<Activity>
     */
    public function applyType(Builder $query, ?string $type): Builder
    {
        return $type === null ? $query : $query->where('type', $type);
    }

    /**
     * @param  Builder<Activity>  $query
     * @return Builder<Activity>
     */
    public function applyDateRange(Builder $query, ?string $dateFrom, ?string $dateTo): Builder
    {
        if ($dateFrom !== null) {
            $query->whereDate('occurred_at', '>=', $dateFrom);
        }

        if ($dateTo !== null) {
            $query->whereDate('occurred_at', '<=', $dateTo);
        }

        return $query;
    }

    /**
     * @param  Builder<Activity>  $query
     * @param  array<string,mixed>  $filters
     * @return Builder<Activity>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyType($query, $filters['type'] ?? null);

        return $this->applyDateRange($query, $filters['date_from'] ?? null, $filters['date_to'] ?? null);
    }
}

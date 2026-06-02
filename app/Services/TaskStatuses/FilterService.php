<?php

namespace App\Services\TaskStatuses;

use App\Models\TaskStatus;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<TaskStatus>  $query
     * @return Builder<TaskStatus>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<TaskStatus>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<TaskStatus>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        return $this->applySearch($query, $filters['search'] ?? null);
    }
}

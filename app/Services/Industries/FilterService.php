<?php

namespace App\Services\Industries;

use App\Models\Industry;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Industry>  $query
     * @return Builder<Industry>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Industry>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Industry>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);

        return $query;
    }
}

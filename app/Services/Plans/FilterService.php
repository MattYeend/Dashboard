<?php

namespace App\Services\Plans;

use App\Services\EscapesLikeValues;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Plan>  $query
     * @return Builder<Plan>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Apply an active-state filter to the query.
     *
     * @param  Builder<Plan>  $query
     * @return Builder<Plan>
     */
    public function applyIsActive(Builder $query, ?bool $isActive): Builder
    {
        if ($isActive === null) {
            return $query;
        }

        return $query->where('is_active', $isActive);
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Plan>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Plan>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyIsActive($query, $filters['is_active'] ?? null);

        return $query;
    }
}

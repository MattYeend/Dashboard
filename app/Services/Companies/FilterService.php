<?php

namespace App\Services\Companies;

use App\Models\Company;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Company>  $query
     * @return Builder<Company>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Company>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Company>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);

        return $query;
    }
}

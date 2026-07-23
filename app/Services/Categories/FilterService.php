<?php

namespace App\Services\Categories;

use App\Models\Category;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function applySearch(
        Builder $query,
        ?string $search
    ): Builder {
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
     * Apply all filters to the query.
     *
     * @param  Builder<Category>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Category>
     */
    public function applyAll(
        Builder $query,
        array $filters
    ): Builder {
        $query = $this->applySearch($query, $filters['search'] ?? null);

        return $query;
    }
}

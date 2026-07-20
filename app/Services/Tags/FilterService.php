<?php

namespace App\Services\Tags;

use App\Models\Tag;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Tag>  $query
     * @return Builder<Tag>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        });
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Tag>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Tag>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        return $this->applySearch($query, $filters['search'] ?? null);
    }
}

<?php

namespace App\Services\Labels;

use App\Models\Label;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Label>  $query
     * @return Builder<Label>
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
     * Apply a background colour filter to the query.
     *
     * @param  Builder<Label>  $query
     * @return Builder<Label>
     */
    public function applyBackgroundColourFilter(Builder $query, ?string $backgroundColour): Builder
    {
        if ($backgroundColour === null) {
            return $query;
        }

        return $query->where('background_colour', $backgroundColour);
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Label>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Label>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyBackgroundColourFilter($query, $filters['background_colour'] ?? null);

        return $query;
    }
}

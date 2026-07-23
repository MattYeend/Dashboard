<?php

namespace App\Services\Industries;

use App\Models\Industry;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Industry>  $query
     * @return Builder<Industry>
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
    public function applyAll(
        Builder $query,
        array $filters
    ): Builder {
        $query = $this->applySearch($query, $filters['search'] ?? null);

        return $query;
    }
}

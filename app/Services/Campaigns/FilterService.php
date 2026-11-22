<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Models\Campaign;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Free-text search over the searchable columns only.
     *
     * @param  Builder<Campaign>  $query
     * @return Builder<Campaign>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $term = $this->escapeLikeValue(trim($search));

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('name', 'like', "%{$term}%");
        });
    }

    /**
     * Filter by status. Non-numeric values are ignored.
     *
     * @param  Builder<Campaign>  $query
     * @return Builder<Campaign>
     */
    public function applyStatus(Builder $query, mixed $value): Builder
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return $query;
        }

        return $query->where('status_id', (int) $value);
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Campaign>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Campaign>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);

        return $this->applyStatus($query, $filters['status_id'] ?? null);
    }
}

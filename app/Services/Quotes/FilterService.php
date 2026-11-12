<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Models\Quote;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply search filter to query.
     *
     * @param  Builder<Quote>  $query
     * @return Builder<Quote>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || $search === '') {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('quote_number', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
        });
    }

    /**
     * Apply status filter to query.
     *
     * @param  Builder<Quote>  $query
     * @return Builder<Quote>
     */
    public function applyStatus(Builder $query, int|string|null $statusId): Builder
    {
        if ($statusId === null || $statusId === '' || ! ctype_digit((string) $statusId)) {
            return $query;
        }

        return $query->where('status_id', (int) $statusId);
    }

    /**
     * Apply all filters to query.
     *
     * @param  Builder<Quote>  $query
     * @param  array<string,mixed>  $filters
     * @return Builder<Quote>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);

        return $this->applyStatus($query, $filters['status_id'] ?? null);
    }
}

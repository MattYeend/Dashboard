<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Models\QuoteItem;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Restrict the query to the lines of one quote.
     *
     * @param  Builder<QuoteItem>  $query
     * @return Builder<QuoteItem>
     */
    public function forQuote(Builder $query, int $quoteId): Builder
    {
        return $query->where('quote_id', $quoteId);
    }

    /**
     * Apply search filter to query.
     *
     * @param  Builder<QuoteItem>  $query
     * @return Builder<QuoteItem>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || $search === '') {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Apply all filters to query.
     *
     * @param  Builder<QuoteItem>  $query
     * @param  array<string,mixed>  $filters
     * @return Builder<QuoteItem>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        return $this->applySearch($query, $filters['search'] ?? null);
    }
}
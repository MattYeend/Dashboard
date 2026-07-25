<?php

namespace App\Services\InvoiceItems;

use App\Models\InvoiceItem;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<InvoiceItem>  $query
     * @return Builder<InvoiceItem>
     */
    public function applySearch(
        Builder $query,
        ?string $search
    ): Builder {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where('description', 'like', "%{$search}%");
    }

    /**
     * Apply a quantity range filter to the query.
     *
     * @param  Builder<InvoiceItem>  $query
     * @return Builder<InvoiceItem>
     */
    public function applyQuantityRangeFilter(
        Builder $query,
        ?int $quantityMin,
        ?int $quantityMax
    ): Builder {
        if ($quantityMin !== null) {
            $query->where('quantity', '>=', $quantityMin);
        }

        if ($quantityMax !== null) {
            $query->where('quantity', '<=', $quantityMax);
        }

        return $query;
    }

    /**
     * Apply a unit price range filter to the query.
     *
     * @param  Builder<InvoiceItem>  $query
     * @return Builder<InvoiceItem>
     */
    public function applyUnitPriceRangeFilter(
        Builder $query,
        ?int $unitPriceMin,
        ?int $unitPriceMax
    ): Builder {
        if ($unitPriceMin !== null) {
            $query->where('unit_price', '>=', $unitPriceMin);
        }

        if ($unitPriceMax !== null) {
            $query->where('unit_price', '<=', $unitPriceMax);
        }

        return $query;
    }

    /**
     * Apply a total range filter to the query.
     *
     * @param  Builder<InvoiceItem>  $query
     * @return Builder<InvoiceItem>
     */
    public function applyTotalRangeFilter(
        Builder $query,
        ?int $totalMin,
        ?int $totalMax
    ): Builder {
        if ($totalMin !== null) {
            $query->where('total', '>=', $totalMin);
        }

        if ($totalMax !== null) {
            $query->where('total', '<=', $totalMax);
        }

        return $query;
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<InvoiceItem>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<InvoiceItem>
     */
    public function applyAll(
        Builder $query,
        array $filters
    ): Builder {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyQuantityRangeFilter(
            $query,
            isset($filters['quantity_min']) ? (int) $filters['quantity_min'] : null,
            isset($filters['quantity_max']) ? (int) $filters['quantity_max'] : null
        );
        $query = $this->applyUnitPriceRangeFilter(
            $query,
            isset($filters['unit_price_min']) ? (int) $filters['unit_price_min'] : null,
            isset($filters['unit_price_max']) ? (int) $filters['unit_price_max'] : null
        );
        $query = $this->applyTotalRangeFilter(
            $query,
            isset($filters['total_min']) ? (int) $filters['total_min'] : null,
            isset($filters['total_max']) ? (int) $filters['total_max'] : null
        );

        return $query;
    }
}

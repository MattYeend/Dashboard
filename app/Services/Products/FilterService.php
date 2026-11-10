<?php

declare(strict_types=1);

namespace App\Services\Products;

use App\Models\Product;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply search filter to query.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || $search === '') {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('sku', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Apply the active/inactive filter to query.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function applyActive(Builder $query, bool|int|string|null $isActive): Builder
    {
        if ($isActive === null || $isActive === '') {
            return $query;
        }

        return $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * Apply a unit filter to query. Only allow-listed units are applied.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function applyUnit(Builder $query, ?string $unit): Builder
    {
        if ($unit === null || ! in_array($unit, Product::UNITS, true)) {
            return $query;
        }

        return $query->where('unit', $unit);
    }

    /**
     * Apply all filters to query.
     *
     * @param  Builder<Product>  $query
     * @param  array<string,mixed>  $filters
     * @return Builder<Product>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyActive($query, $filters['is_active'] ?? null);

        return $this->applyUnit($query, $filters['unit'] ?? null);
    }
}
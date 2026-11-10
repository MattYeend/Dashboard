<?php

declare(strict_types=1);

namespace App\Services\Products;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to query.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'name',
        ?string $sortDirection = 'asc'
    ): Builder {
        $sortBy = $sortBy ?? 'name';
        $sortDirection = strtolower(
            $sortDirection ?? 'asc'
        ) === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'sku' => $query->orderBy('sku', $sortDirection),
            'name' => $query->orderBy('name', $sortDirection),
            'unit' => $query->orderBy('unit', $sortDirection),
            'unit_price' => $query->orderBy('unit_price', $sortDirection),
            'cost_price' => $query->orderBy('cost_price', $sortDirection),
            'tax_rate' => $query->orderBy('tax_rate', $sortDirection),
            'is_active' => $query->orderBy('is_active', $sortDirection),
            'updated_at' => $query->orderBy('updated_at', $sortDirection),
            default => $query->orderBy('created_at', $sortDirection),
        };
    }

    /**
     * Get available sort fields.
     *
     * @return array<string, string>
     */
    public function getAvailableSortFields(): array
    {
        return [
            'sku' => 'SKU',
            'name' => 'Name',
            'unit' => 'Unit',
            'unit_price' => 'Unit Price',
            'cost_price' => 'Cost Price',
            'tax_rate' => 'Tax Rate',
            'is_active' => 'Active',
            'created_at' => 'Created Date',
        ];
    }
}

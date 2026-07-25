<?php

namespace App\Services\InvoiceItems;

use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query.
     *
     * @param  Builder<InvoiceItem>  $query
     * @return Builder<InvoiceItem>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'position',
        ?string $sortDirection = 'asc'
    ): Builder {
        $sortBy = $sortBy ?? 'position';
        $sortDirection = strtolower($sortDirection ?? 'asc') === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'description' => $query->orderBy('description', $sortDirection),
            'quantity' => $query->orderBy('quantity', $sortDirection),
            'unit_price' => $query->orderBy('unit_price', $sortDirection),
            'total' => $query->orderBy('total', $sortDirection),
            'updated_at' => $query->orderBy('updated_at', $sortDirection),
            default => $query->orderBy('position', $sortDirection),
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
            'position' => 'Order',
            'description' => 'Description',
            'quantity' => 'Quantity',
            'unit_price' => 'Unit Price',
            'total' => 'Total',
        ];
    }
}

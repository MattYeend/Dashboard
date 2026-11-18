<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Models\QuoteItem;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to query.
     *
     * @param  Builder<QuoteItem>  $query
     * @return Builder<QuoteItem>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'position',
        ?string $sortDirection = 'asc'
    ): Builder {
        $sortBy = $sortBy ?? 'position';
        $sortDirection = strtolower(
            $sortDirection ?? 'asc'
        ) === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'name' => $query->orderBy('name', $sortDirection),
            'quantity' => $query->orderBy('quantity', $sortDirection),
            'unit_price' => $query->orderBy('unit_price', $sortDirection),
            'line_total' => $query->orderBy('line_total', $sortDirection),
            'created_at' => $query->orderBy('created_at', $sortDirection),
            default => $query->orderBy('position', $sortDirection)->orderBy('id', $sortDirection),
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
            'position' => 'Position',
            'name' => 'Name',
            'quantity' => 'Quantity',
            'unit_price' => 'Unit Price',
            'line_total' => 'Line Total',
        ];
    }
}

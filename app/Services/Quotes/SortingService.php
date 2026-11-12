<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Models\Quote;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to query.
     *
     * @param  Builder<Quote>  $query
     * @return Builder<Quote>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'desc'
    ): Builder {
        $sortBy = $sortBy ?? 'created_at';
        $sortDirection = strtolower(
            $sortDirection ?? 'desc'
        ) === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'quote_number' => $query->orderBy('quote_number', $sortDirection),
            'title' => $query->orderBy('title', $sortDirection),
            'subtotal' => $query->orderBy('subtotal', $sortDirection),
            'discount_amount' => $query->orderBy('discount_amount', $sortDirection),
            'tax_amount' => $query->orderBy('tax_amount', $sortDirection),
            'total_amount' => $query->orderBy('total_amount', $sortDirection),
            'issued_at' => $query->orderBy('issued_at', $sortDirection),
            'valid_until' => $query->orderBy('valid_until', $sortDirection),
            'status_id' => $query->orderBy('status_id', $sortDirection),
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
            'quote_number' => 'Quote Number',
            'title' => 'Title',
            'subtotal' => 'Subtotal',
            'discount_amount' => 'Discount',
            'tax_amount' => 'Tax',
            'total_amount' => 'Total',
            'issued_at' => 'Issued Date',
            'valid_until' => 'Valid Until',
            'status_id' => 'Status',
            'created_at' => 'Created Date',
        ];
    }
}

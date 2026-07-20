<?php

namespace App\Services\Orders;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to query.
     *
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'ordered_at',
        ?string $sortDirection = 'asc'
    ): Builder {
        $sortBy = $sortBy ?? 'ordered_at';
        $sortDirection = strtolower(
            $sortDirection ?? 'asc'
        ) === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'order_number' => $query->orderBy('order_number', $sortDirection),
            'title' => $query->orderBy('title', $sortDirection),
            'subtotal' => $query->orderBy('subtotal', $sortDirection),
            'discount_amount' => $query->orderBy('discount_amount', $sortDirection),
            'tax_amount' => $query->orderBy('tax_amount', $sortDirection),
            'total_amount' => $query->orderBy('total_amount', $sortDirection),
            'ordered_at' => $query->orderBy('ordered_at', $sortDirection),
            'due_at' => $query->orderBy('due_at', $sortDirection),
            'completed_at' => $query->orderBy('completed_at', $sortDirection),
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
            'order_number' => 'Order Number',
            'title' => 'Title',
            'subtotal' => 'Subtotal',
            'discount_amount' => 'Discount',
            'tax_amount' => 'Tax',
            'total_amount' => 'Total',
            'ordered_at' => 'Ordered Date',
            'due_at' => 'Due Date',
            'completed_at' => 'Completed Date',
            'status_id' => 'Status',
            'created_at' => 'Created Date',
        ];
    }
}

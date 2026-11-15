<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to query.
     *
     * @param  Builder<Payment>  $query
     * @return Builder<Payment>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'paid_at',
        ?string $sortDirection = 'desc'
    ): Builder {
        $sortBy = $sortBy ?? 'paid_at';
        $sortDirection = strtolower(
            $sortDirection ?? 'desc'
        ) === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'paid_at' => $query->orderBy('paid_at', $sortDirection),
            'amount' => $query->orderBy('amount', $sortDirection),
            'method' => $query->orderBy('method', $sortDirection),
            'type' => $query->orderBy('type', $sortDirection),
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
            'paid_at' => 'Date received',
            'amount' => 'Amount',
            'method' => 'Method',
            'type' => 'Type',
            'created_at' => 'Created Date',
        ];
    }
}

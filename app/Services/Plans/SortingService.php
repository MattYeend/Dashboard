<?php

namespace App\Services\Plans;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query.
     *
     * @param  Builder<Plan>  $query
     * @return Builder<Plan>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'asc'
    ): Builder {
        $sortBy = $sortBy ?? 'created_at';
        $sortDirection = strtolower($sortDirection ?? 'asc') === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'name' => $query->orderBy('name', $sortDirection),
            'price_per_user_per_month' => $query->orderBy('price_per_user_per_month', $sortDirection),
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
            'name' => 'Name',
            'price_per_user_per_month' => 'Price',
            'is_active' => 'Active',
            'created_at' => 'Created Date',
        ];
    }
}

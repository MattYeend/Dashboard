<?php

declare(strict_types=1);

namespace App\Services\PaymentStatuses;

use App\Models\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to query.
     *
     * @param  Builder<PaymentStatus>  $query
     * @return Builder<PaymentStatus>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'title',
        ?string $sortDirection = 'asc'
    ): Builder {
        $sortBy = $sortBy ?? 'title';
        $sortDirection = strtolower(
            $sortDirection ?? 'asc'
        ) === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'title' => $query->orderBy('title', $sortDirection),
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
            'title' => 'Title',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
        ];
    }
}

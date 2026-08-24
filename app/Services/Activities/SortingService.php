<?php

namespace App\Services\Activities;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * @param  Builder<Activity>  $query
     * @return Builder<Activity>
     */
    public function applySorting(Builder $query, ?string $sortBy = 'occurred_at', ?string $sortDirection = 'desc'): Builder
    {
        $sortBy = $sortBy ?? 'occurred_at';
        $sortDirection = strtolower($sortDirection ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'type' => $query->orderBy('type', $sortDirection),
            'created_at' => $query->orderBy('created_at', $sortDirection),
            default => $query->orderBy('occurred_at', $sortDirection),
        };
    }

    /**
     * @return array<string, string>
     */
    public function getAvailableSortFields(): array
    {
        return [
            'occurred_at' => 'Occurred at',
            'type' => 'Type',
            'created_at' => 'Logged at',
        ];
    }
}

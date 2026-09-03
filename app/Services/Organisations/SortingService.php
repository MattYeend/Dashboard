<?php

namespace App\Services\Organisations;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query.
     *
     * @param  Builder<Organisation>  $query
     * @return Builder<Organisation>
     */
    public function applySorting(Builder $query, ?string $sortBy = 'name', ?string $sortDirection = 'asc'): Builder
    {
        $sortBy = $sortBy ?? 'name';
        $sortDirection = strtolower($sortDirection ?? 'asc') === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'created_at' => $query->orderBy('created_at', $sortDirection),
            'updated_at' => $query->orderBy('updated_at', $sortDirection),
            default => $query->orderBy('name', $sortDirection),
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
            'created_at' => 'Created Date',
        ];
    }
}

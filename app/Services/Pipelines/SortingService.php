<?php

namespace App\Services\Pipelines;

use App\Models\Pipeline;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query.
     *
     * @param  Builder<Pipeline>  $query
     * @return Builder<Pipeline>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'title',
        ?string $sortDirection = 'desc'
    ): Builder {
        $sortBy = $sortBy ?? 'title';
        $sortDirection = strtolower(
            $sortDirection ?? 'asc'
        ) === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'title' => $query->orderBy('title', $sortDirection),
            'is_default' => $query->orderBy('is_default', $sortDirection),
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
            'is_default' => 'Default',
            'created_at' => 'Created Date',
        ];
    }
}

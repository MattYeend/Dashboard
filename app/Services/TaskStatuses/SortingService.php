<?php

namespace App\Services\TaskStatuses;

use App\Models\TaskStatus;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query.
     *
     * @param  Builder<TaskStatus>  $query
     * @return Builder<TaskStatus>
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

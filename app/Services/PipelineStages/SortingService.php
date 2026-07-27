<?php

namespace App\Services\PipelineStages;

use App\Models\PipelineStage;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query.
     *
     * @param  Builder<PipelineStage>  $query
     * @return Builder<PipelineStage>
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
            'title' => $query->orderBy('title', $sortDirection),
            'position' => $query->orderBy('position', $sortDirection),
            'is_won' => $query->orderBy('is_won', $sortDirection),
            'is_lost' => $query->orderBy('is_lost', $sortDirection),
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
            'position' => 'Position',
            'title' => 'Title',
            'is_won' => 'Won',
            'is_lost' => 'Lost',
            'created_at' => 'Created Date',
        ];
    }
}
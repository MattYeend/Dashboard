<?php

declare(strict_types=1);

namespace App\Services\Questions;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to query.
     *
     * @param  Builder<Question>  $query
     * @return Builder<Question>
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

        $query = match ($sortBy) {
            'question' => $query->orderBy('question', $sortDirection),
            'type' => $query->orderBy('type', $sortDirection),
            'points' => $query->orderBy('points', $sortDirection),
            'created_at' => $query->orderBy('created_at', $sortDirection),
            'updated_at' => $query->orderBy('updated_at', $sortDirection),
            default => $query->orderBy('position', $sortDirection),
        };

        return $query->orderBy('id');
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
            'question' => 'Question',
            'type' => 'Type',
            'points' => 'Points',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
        ];
    }
}

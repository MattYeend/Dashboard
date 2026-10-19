<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query using an allow-list of sortable fields.
     *
     * @param  Builder<Lesson>  $query
     * @return Builder<Lesson>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'position',
        ?string $sortDirection = 'asc'
    ): Builder {
        $sortBy = $sortBy ?? 'position';
        $sortDirection = strtolower($sortDirection ?? 'asc') === 'asc' ? 'asc' : 'desc';

        $query = match ($sortBy) {
            'title' => $query->orderBy('title', $sortDirection),
            'duration_minutes' => $query->orderBy('duration_minutes', $sortDirection),
            'published_at' => $query->orderBy('published_at', $sortDirection),
            'created_at' => $query->orderBy('created_at', $sortDirection),
            default => $query->orderBy('course_id')->orderBy('position', $sortDirection),
        };

        // Deterministic tie-breaker so pagination never repeats or skips rows.
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
            'title' => 'Title',
            'position' => 'Position',
            'duration_minutes' => 'Duration',
            'published_at' => 'Published Date',
            'created_at' => 'Created Date',
        ];
    }
}

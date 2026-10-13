<?php

declare(strict_types=1);

namespace App\Services\Courses;

use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query.
     *
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'asc'
    ): Builder {
        $sortBy = $sortBy ?? 'created_at';
        $sortDirection = strtolower($sortDirection ?? 'asc') === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'title' => $query->orderBy('title', $sortDirection),
            'difficulty' => $query->orderBy('difficulty', $sortDirection),
            'estimated_duration_minutes' => $query->orderBy('estimated_duration_minutes', $sortDirection),
            'published_at' => $query->orderBy('published_at', $sortDirection),
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
            'difficulty' => 'Difficulty',
            'estimated_duration_minutes' => 'Duration',
            'published_at' => 'Published Date',
            'created_at' => 'Created Date',
        ];
    }
}

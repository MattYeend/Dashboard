<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query using an allow-list of columns.
     *
     * @param  Builder<Quiz>  $query
     * @return Builder<Quiz>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'title',
        ?string $sortDirection = 'asc'
    ): Builder {
        $sortBy ??= 'title';
        $sortDirection = strtolower($sortDirection ?? 'asc') === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'title' => $query->orderBy('title', $sortDirection),
            'pass_mark_percentage' => $query->orderBy('pass_mark_percentage', $sortDirection),
            'published_at' => $query->orderBy('published_at', $sortDirection),
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
            'pass_mark_percentage' => 'Pass Mark',
            'published_at' => 'Published Date',
            'created_at' => 'Created Date',
        ];
    }
}

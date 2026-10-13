<?php

declare(strict_types=1);

namespace App\Services\Courses;

use App\Models\Course;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    public const DIFFICULTIES = ['beginner', 'intermediate', 'advanced'];

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('summary', 'like', "%{$search}%");
        });
    }

    /**
     * Apply difficulty filter to the query.
     *
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function applyDifficultyFilter(Builder $query, ?string $difficulty): Builder
    {
        if ($difficulty === null || ! in_array($difficulty, self::DIFFICULTIES, true)) {
            return $query;
        }

        return $query->where('difficulty', $difficulty);
    }

    /**
     * Apply instructor filter to the query.
     *
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function applyInstructorFilter(Builder $query, ?int $instructorId): Builder
    {
        if ($instructorId === null) {
            return $query;
        }

        return $query->where('instructor_id', $instructorId);
    }

    /**
     * Apply published state filter ('published' or 'draft') to the query.
     *
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function applyPublishedFilter(Builder $query, ?string $published): Builder
    {
        return match ($published) {
            'published' => $query->whereNotNull('published_at')->where('published_at', '<=', now()),
            'draft' => $query->where(function (Builder $q): void {
                $q->whereNull('published_at')->orWhere('published_at', '>', now());
            }),
            default => $query,
        };
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Course>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Course>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyDifficultyFilter($query, $filters['difficulty'] ?? null);
        $query = $this->applyInstructorFilter(
            $query,
            isset($filters['instructor_id']) ? (int) $filters['instructor_id'] : null
        );

        return $this->applyPublishedFilter($query, $filters['published'] ?? null);
    }
}

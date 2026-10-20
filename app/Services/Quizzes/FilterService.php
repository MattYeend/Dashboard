<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\Models\Quiz;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Publication states derived from published_at.
     *
     * @var array<int, string>
     */
    public const STATUSES = ['draft', 'scheduled', 'published'];

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Quiz>  $query
     * @return Builder<Quiz>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || $search === '') {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where('title', 'like', "%{$search}%");
    }

    /**
     * Apply a course filter to the query.
     *
     * @param  Builder<Quiz>  $query
     * @return Builder<Quiz>
     */
    public function applyCourse(Builder $query, ?int $courseId): Builder
    {
        return $courseId === null ? $query : $query->where('course_id', $courseId);
    }

    /**
     * Apply a lesson filter to the query.
     *
     * @param  Builder<Quiz>  $query
     * @return Builder<Quiz>
     */
    public function applyLesson(Builder $query, ?int $lessonId): Builder
    {
        return $lessonId === null ? $query : $query->where('lesson_id', $lessonId);
    }

    /**
     * Apply a status filter to the query. Unknown statuses are ignored.
     *
     * @param  Builder<Quiz>  $query
     * @return Builder<Quiz>
     */
    public function applyStatus(Builder $query, ?string $status): Builder
    {
        return match ($status) {
            'draft' => $query->whereNull('published_at'),
            'scheduled' => $query->where('published_at', '>', now()),
            'published' => $query->published(),
            default => $query,
        };
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Quiz>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Quiz>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyCourse($query, isset($filters['course_id']) ? (int) $filters['course_id'] : null);
        $query = $this->applyLesson($query, isset($filters['lesson_id']) ? (int) $filters['lesson_id'] : null);

        return $this->applyStatus($query, $filters['status'] ?? null);
    }
}

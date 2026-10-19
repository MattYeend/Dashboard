<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Models\Lesson;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Lesson>  $query
     * @return Builder<Lesson>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $search = $this->escapeLikeValue(trim($search));

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('summary', 'like', "%{$search}%");
        });
    }

    /**
     * Restrict the query to a single course.
     *
     * @param  Builder<Lesson>  $query
     * @return Builder<Lesson>
     */
    public function applyCourse(Builder $query, mixed $courseId): Builder
    {
        if (! is_numeric($courseId)) {
            return $query;
        }

        return $query->where('course_id', (int) $courseId);
    }

    /**
     * Restrict the query to preview or non-preview lessons.
     *
     * @param  Builder<Lesson>  $query
     * @return Builder<Lesson>
     */
    public function applyPreview(Builder $query, mixed $isPreview): Builder
    {
        $value = filter_var($isPreview, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($value === null) {
            return $query;
        }

        return $query->where('is_preview', $value);
    }

    /**
     * Restrict the query by publication status (published, draft, scheduled).
     *
     * @param  Builder<Lesson>  $query
     * @return Builder<Lesson>
     */
    public function applyStatus(Builder $query, ?string $status): Builder
    {
        return match ($status) {
            'published' => $query->whereNotNull('published_at')->where('published_at', '<=', now()),
            'scheduled' => $query->where('published_at', '>', now()),
            'draft' => $query->whereNull('published_at'),
            default => $query,
        };
    }

    /**
     * Limit the query to lessons visible to learners: the lesson and its
     * course must both be published.
     *
     * @param  Builder<Lesson>  $query
     * @return Builder<Lesson>
     */
    public function applyPublishedOnly(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('course', function (Builder $course): void {
                $course->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
            });
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Lesson>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Lesson>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyCourse($query, $filters['course_id'] ?? null);
        $query = $this->applyPreview($query, $filters['is_preview'] ?? null);

        return $this->applyStatus($query, $filters['status'] ?? null);
    }
}

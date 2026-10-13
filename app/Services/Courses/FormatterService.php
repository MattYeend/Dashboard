<?php

declare(strict_types=1);

namespace App\Services\Courses;

use App\Models\Course;

class FormatterService
{
    /**
     * Format a single course with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Course $course): array
    {
        return [
            'id' => $course->id,
            'organisation_id' => $course->organisation_id,
            'title' => $course->title,
            'slug' => $course->slug,
            'summary' => $course->summary,
            'description' => $course->description,
            'difficulty' => $course->difficulty,
            'estimated_duration_minutes' => $course->estimated_duration_minutes,
            'instructor_id' => $course->instructor_id,
            'published_at' => $course->published_at?->format('Y-m-d H:i:s'),
            'is_published' => $course->published_at !== null && $course->published_at->isPast(),
            'meta' => $course->meta,
            'created_at' => $course->created_at,
            'updated_at' => $course->updated_at,
            'deleted_at' => $course->deleted_at,
            'restored_at' => $course->restored_at,
            'creator' => $course->creator ? ['id' => $course->creator->id, 'name' => $course->creator->name] : null,
            'updater' => $course->updater ? ['id' => $course->updater->id, 'name' => $course->updater->name] : null,
            'deleter' => $course->deleter ? ['id' => $course->deleter->id, 'name' => $course->deleter->name] : null,
            'restorer' => $course->restorer ? ['id' => $course->restorer->id, 'name' => $course->restorer->name] : null,
            'instructor' => $course->instructor ? [
                'id' => $course->instructor->id,
                'name' => $course->instructor->name,
            ] : null,
        ];
    }
}

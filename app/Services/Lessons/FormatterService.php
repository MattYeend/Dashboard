<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Models\Lesson;

class FormatterService
{
    /**
     * Inject the required services into the formatter service.
     */
    public function __construct(
        protected readonly VideoUrlService $videoUrlService,
    ) {}

    /**
     * Format a single lesson. Content is omitted for list views.
     *
     * @return array<string, mixed>
     */
    public function format(Lesson $lesson, bool $includeContent = true): array
    {
        return [
            'id' => $lesson->id,
            'course_id' => $lesson->course_id,
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'summary' => $lesson->summary,
            ...($includeContent ? ['content' => $lesson->content] : []),
            'video_url' => $lesson->video_url,
            'embed_url' => $this->videoUrlService->embedUrl($lesson->video_url),
            'position' => $lesson->position,
            'duration_minutes' => $lesson->duration_minutes,
            'is_preview' => $lesson->is_preview,
            'published_at' => $lesson->published_at,
            'meta' => $lesson->meta,
            'created_at' => $lesson->created_at,
            'updated_at' => $lesson->updated_at,
            'deleted_at' => $lesson->deleted_at,
            'restored_at' => $lesson->restored_at,
            'created_by' => $lesson->created_by,
            'updated_by' => $lesson->updated_by,
            'deleted_by' => $lesson->deleted_by,
            'restored_by' => $lesson->restored_by,
            'course' => $lesson->course ? ['id' => $lesson->course->id, 'title' => $lesson->course->title] : null,
            'creator' => $lesson->creator ? ['id' => $lesson->creator->id, 'name' => $lesson->creator->name] : null,
            'updater' => $lesson->updater ? ['id' => $lesson->updater->id, 'name' => $lesson->updater->name] : null,
            'deleter' => $lesson->deleter ? ['id' => $lesson->deleter->id, 'name' => $lesson->deleter->name] : null,
            'restorer' => $lesson->restorer ? ['id' => $lesson->restorer->id, 'name' => $lesson->restorer->name] : null,
        ];
    }
}

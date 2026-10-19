<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Models\Course;
use App\Models\Lesson;
use App\Services\SlugService;
use InvalidArgumentException;

class DataPreparationService
{
    /**
     * Inject the required services into the data preparation service.
     */
    public function __construct(
        protected readonly SlugService $slugService,
        protected readonly PositionService $positionService,
        protected readonly VideoUrlService $videoUrlService,
    ) {}

    /**
     * Prepare lesson data for creation. Call inside a transaction, as the
     * next position is read with a row lock.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException
     */
    public function prepareForCreation(array $data): array
    {
        $course = Course::query()->findOrFail((int) $data['course_id']);
        $title = strip_tags($data['title']);

        return [
            'organisation_id' => $course->organisation_id,
            'course_id' => $course->id,
            'title' => $title,
            'slug' => $data['slug'] ?? $this->slugService->generateUnique(
                Lesson::class,
                $title,
                scope: ['course_id' => $course->id],
            ),
            'summary' => $this->cleanText($data['summary'] ?? null),
            'content' => $this->cleanText($data['content'] ?? null),
            'video_url' => $this->cleanVideoUrl($data['video_url'] ?? null),
            'position' => $this->positionService->next($course->id),
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'is_preview' => (bool) ($data['is_preview'] ?? false),
            'published_at' => $data['published_at'] ?? null,
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare lesson data for update. Call inside a transaction, as moving a
     * lesson to another course reads the next position with a row lock.
     *
     * Position only changes here when the lesson moves course (it goes to the
     * end of the new course). Manual reordering goes through
     * PositionService::reorder().
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException
     */
    public function prepareForUpdate(array $data, Lesson $lesson): array
    {
        $payload = [];

        if (array_key_exists('title', $data)) {
            $payload['title'] = strip_tags($data['title']);
        }

        if (! empty($data['slug'])) {
            $payload['slug'] = $data['slug'];
        }

        if (
            array_key_exists('course_id', $data)
            && (int) $data['course_id'] !== $lesson->course_id
        ) {
            $course = Course::query()->findOrFail((int) $data['course_id']);

            // Defence in depth behind the Form Request: never cross tenants.
            if ($course->organisation_id !== $lesson->organisation_id) {
                throw new InvalidArgumentException('A lesson cannot be moved to another organisation.');
            }

            $payload['course_id'] = $course->id;
            $payload['position'] = $this->positionService->next($course->id);

            // Keep the existing slug unless it collides in the new course.
            if (empty($data['slug'])) {
                $payload['slug'] = $this->slugService->generateUnique(
                    Lesson::class,
                    $lesson->slug,
                    $lesson->id,
                    scope: ['course_id' => $course->id],
                );
            }
        }

        if (array_key_exists('summary', $data)) {
            $payload['summary'] = $this->cleanText($data['summary']);
        }

        if (array_key_exists('content', $data)) {
            $payload['content'] = $this->cleanText($data['content']);
        }

        if (array_key_exists('video_url', $data)) {
            $payload['video_url'] = $this->cleanVideoUrl($data['video_url']);
        }

        if (array_key_exists('duration_minutes', $data)) {
            $payload['duration_minutes'] = $data['duration_minutes'];
        }

        if (array_key_exists('is_preview', $data)) {
            $payload['is_preview'] = (bool) $data['is_preview'];
        }

        if (array_key_exists('published_at', $data)) {
            $payload['published_at'] = $data['published_at'];
        }

        if (array_key_exists('meta', $data)) {
            $payload['meta'] = $data['meta'];
        }

        return $payload;
    }

    /**
     * Strip markup and return null for empty text.
     */
    protected function cleanText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(strip_tags($value));

        return $value === '' ? null : $value;
    }

    /**
     * Enforce the embeddable host allow-list as a second line of defence
     * behind the Form Request.
     *
     * @throws InvalidArgumentException
     */
    protected function cleanVideoUrl(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (! $this->videoUrlService->isAllowed($url)) {
            throw new InvalidArgumentException('The video URL must be an https YouTube or Vimeo link.');
        }

        return $url;
    }
}

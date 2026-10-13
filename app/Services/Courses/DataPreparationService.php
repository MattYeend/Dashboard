<?php

declare(strict_types=1);

namespace App\Services\Courses;

use App\Models\Course;
use App\Services\SlugService;
use Mews\Purifier\Facades\Purifier;

class DataPreparationService
{
    /**
     * Inject the required services into the data preparation service.
     */
    public function __construct(
        protected readonly SlugService $slugService
    ) {}

    /**
     * Prepare course data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'title' => $data['title'],
            'slug' => $this->slugService->generateUnique(Course::class, $data['title']),
            'summary' => $data['summary'] ?? null,
            'description' => $this->clean($data['description'] ?? null),
            'difficulty' => $data['difficulty'] ?? 'beginner',
            'estimated_duration_minutes' => $data['estimated_duration_minutes'] ?? null,
            'instructor_id' => $data['instructor_id'] ?? null,
            'published_at' => $data['published_at'] ?? null,
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare course data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(Course $course, array $data): array
    {
        $allowed = [
            'title',
            'summary',
            'description',
            'difficulty',
            'estimated_duration_minutes',
            'instructor_id',
            'published_at',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (array_key_exists('description', $payload)) {
            $payload['description'] = $this->clean($payload['description']);
        }

        if (isset($payload['title']) && $payload['title'] !== $course->title) {
            $payload['slug'] = $this->slugService->generateUnique(
                Course::class,
                $payload['title'],
                $course->id,
            );
        }

        return $payload;
    }

    /**
     * Purify rich text, keeping null as null.
     */
    private function clean(?string $html): ?string
    {
        return $html === null || trim($html) === '' ? null : Purifier::clean($html);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\Services\SlugService;

class DataPreparationService
{
    /**
     * Inject the required services into the data preparation service.
     */
    public function __construct(
        protected readonly SlugService $slugService,
    ) {}

    /**
     * Settings that fall back to database defaults when not supplied.
     *
     * @var array<int, string>
     */
    protected const OPTIONAL_SETTINGS = [
        'pass_mark_percentage',
        'time_limit_minutes',
        'max_attempts',
        'shuffle_questions',
        'show_correct_answers',
    ];

    /**
     * Prepare quiz attributes for creation, stamping audit columns.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data, int $actorId): array
    {
        $payload = [
            'title' => $data['title'],
            'slug' => $this->slugService->generateUnique($data['title'], 'quizzes'),
            'description' => $this->purify($data['description'] ?? null),
            'course_id' => $data['course_id'],
            'lesson_id' => $data['lesson_id'] ?? null,
            'published_at' => $data['published_at'] ?? null,
            'meta' => $data['meta'] ?? null,
            'created_by' => $actorId,
        ];

        foreach (self::OPTIONAL_SETTINGS as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $payload[$field] = $data[$field];
            }
        }

        return $payload;
    }

    /**
     * Prepare quiz attributes for update. Only allow-listed fields pass.
     * The slug is regenerated only when the title changes.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(
        array $data,
        ?int $quizId = null,
        ?string $currentTitle = null
    ): array {
        $allowed = [
            'title',
            'description',
            'course_id',
            'lesson_id',
            'pass_mark_percentage',
            'time_limit_minutes',
            'max_attempts',
            'shuffle_questions',
            'show_correct_answers',
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
            $payload['description'] = $this->purify($payload['description']);
        }

        if (isset($payload['title']) && $payload['title'] !== $currentTitle) {
            $payload['slug'] = $this->slugService->generateUnique($payload['title'], 'quizzes', $quizId);
        }

        return $payload;
    }

    /**
     * Pull the question selections out of validated input.
     * Returns null when the key is absent so an update never wipes questions.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, array{question_id: int, points: int|null}>|null
     */
    public function extractQuestionSelections(array $data): ?array
    {
        if (! array_key_exists('questions', $data) || ! is_array($data['questions'])) {
            return null;
        }

        return array_map(
            static fn (array $selection): array => [
                'question_id' => (int) $selection['question_id'],
                'points' => isset($selection['points']) ? (int) $selection['points'] : null,
            ],
            array_values($data['questions'])
        );
    }

    /**
     * Strip unsafe markup from rich text. Single place to swap the purifier.
     */
    protected function purify(?string $html): ?string
    {
        return $html === null || $html === '' ? null : clean($html);
    }
}

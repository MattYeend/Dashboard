<?php

declare(strict_types=1);

namespace App\Services\Answers;

class DataPreparationService
{
    /**
     * Prepare answer data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'question_id' => $data['question_id'],
            'answer' => $data['answer'],
            'is_correct' => (bool) ($data['is_correct'] ?? false),
            'feedback' => $data['feedback'] ?? null,
            'position' => $data['position'] ?? 0,
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare answer data for update.
     *
     * The question cannot be changed, and fields that were not sent are
     * left untouched.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        $allowed = [
            'answer',
            'is_correct',
            'feedback',
            'position',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        return $payload;
    }
}

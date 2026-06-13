<?php

namespace App\Services\TaskStatuses;

class DataPreparationService
{
    /**
     * Prepare task status data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data, int $createdBy): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'background_colour' => $data['background_colour'] ?? '#ffffff',
            'text_colour' => $data['text_colour'] ?? '#000000',
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare task status data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
    {
        return array_filter([
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'background_colour' => $data['background_colour'] ?? null,
            'text_colour' => $data['text_colour'] ?? null,
            'meta' => $data['meta'] ?? null,
            'updated_by' => $updatedBy,
        ], fn (mixed $value): bool => $value !== null);
    }
}

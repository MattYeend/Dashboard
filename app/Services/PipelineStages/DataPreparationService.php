<?php

namespace App\Services\PipelineStages;

class DataPreparationService
{
    /**
     * Prepare pipeline stage data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data, int $pipelineId, int $createdBy): array
    {
        return [
            'pipeline_id' => $pipelineId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'position' => $data['position'] ?? 0,
            'background_colour' => $data['background_colour'] ?? '#e5e7eb',
            'text_colour' => $data['text_colour'] ?? '#111827',
            'is_won' => $data['is_won'] ?? false,
            'is_lost' => $data['is_lost'] ?? false,
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare pipeline stage data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
    {
        $allowed = [
            'title',
            'description',
            'position',
            'background_colour',
            'text_colour',
            'is_won',
            'is_lost',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        $payload['updated_by'] = $updatedBy;

        return $payload;
    }
}
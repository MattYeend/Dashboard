<?php

namespace App\Services\Pipelines;

class DataPreparationService
{
    /**
     * Prepare pipeline data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_default' => $data['is_default'] ?? false,
            'status_id' => $data['status_id'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare pipeline data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        $allowed = [
            'title',
            'description',
            'is_default',
            'status_id',
            'assigned_to',
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

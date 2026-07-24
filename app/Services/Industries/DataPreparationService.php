<?php

namespace App\Services\Industries;

class DataPreparationService
{
    /**
     * Prepare industry data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data, int $createdBy): array
    {
        return [
            'title' => $data['title'],
            'code' => $data['code'] ?? null,
            'description' => $data['description'] ?? null,
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare industry data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
    {
        $allowed = [
            'title',
            'code',
            'description',
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

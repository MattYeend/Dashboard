<?php

namespace App\Services\Posts;

use Mews\Purifier\Facades\Purifier;

class DataPreparationService
{
    /**
     * Prepare post data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data, int $createdBy): array
    {
        return [
            'title' => $data['title'],
            'description' => Purifier::clean($data['description']),
            'image' => $data['image'] ?? null,
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare post data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
    {
        $allowed = [
            'title',
            'description',
            'image',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }

            if ($field === 'description' && array_key_exists($field, $data)) {
                $payload[$field] = Purifier::clean($data[$field]);
                continue;
            }
        }

        $payload['updated_by'] = $updatedBy;

        return $payload;
    }
}

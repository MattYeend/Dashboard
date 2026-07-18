<?php

namespace App\Services\InvoiceStatuses;

class DataPreparationService
{
    /**
     * Prepare invoice status data for creation.
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
     * Prepare invoice status data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
    {
        $allowed = [
            'title',
            'description',
            'background_colour',
            'text_colour',
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

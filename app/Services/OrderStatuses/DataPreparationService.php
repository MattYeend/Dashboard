<?php

namespace App\Services\OrderStatuses;

class DataPreparationService
{
    /**
     * Prepare order status data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'background_colour' => $data['background_colour'] ?? '#ffffff',
            'text_colour' => $data['text_colour'] ?? '#000000',
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare order status data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
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

        return $payload;
    }
}

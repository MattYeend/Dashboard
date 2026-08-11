<?php

namespace App\Services\Labels;

use Illuminate\Support\Str;

class DataPreparationService
{
    /**
     * Prepare label data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data, int $createdBy): array
    {
        return [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'background_colour' => $data['background_colour'] ?? '#6b7280',
            'text_colour' => $data['text_colour'] ?? '#ffffff',
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare label data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
    {
        $allowed = [
            'name',
            'slug',
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

        if (array_key_exists('name', $data) && ! array_key_exists('slug', $data)) {
            $payload['slug'] = Str::slug($data['name']);
        }

        $payload['updated_by'] = $updatedBy;

        return $payload;
    }
}

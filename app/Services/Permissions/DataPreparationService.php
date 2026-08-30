<?php

namespace App\Services\Permissions;

class DataPreparationService
{
    /**
     * Prepare permission data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'name' => trim($data['name']),
            'guard_name' => trim($data['guard_name'] ?? 'web'),
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare permission data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        $allowed = ['name', 'guard_name', 'meta'];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = is_string($data[$field]) ? trim($data[$field]) : $data[$field];
            }
        }

        return $payload;
    }
}

<?php

namespace App\Services\Users;

class DataPreparationService
{
    /**
     * Prepare user data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(
        array $data,
        int $createdBy
    ): array {
        return [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? 'user',
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare user data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string,mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
    {
        $allowed = [
            'name',
            'email',
            'password',
            'role',
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

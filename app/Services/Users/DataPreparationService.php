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
    ): array {
        return [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'locale' => $data['locale'] ?? 'en_GB',
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare user data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string,mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        $allowed = [
            'name',
            'email',
            'password',
            'locale',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if ($field === 'password' && empty($data['password'])) {
                continue;
            }

            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        return $payload;
    }
}

<?php

namespace App\Services\Users;

class DataPreparationService
{
    /**
     * Prepare contact data for creation.
     *
     * @param  array<string, mixed> $data
     * @param  string $contactableType
     * @param  int $contactableId
     *
     * @return array<string, mixed>
     */
    public function prepareForCreation(
        array $data,
        string $contactableType,
        int $contactableId
    ): array {
        return [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? 'user',
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare contact data for update.
     *
     * @param  array<string, mixed> $data
     *
     * @return array<string,mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        return array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'role' => $data['role'] ?? null,
            'meta' => $data['meta'] ?? null,
        ], fn (mixed $value): bool => $value !== null);
    }
}

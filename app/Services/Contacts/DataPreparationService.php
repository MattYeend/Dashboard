<?php

namespace App\Services\Contacts;

class DataPreparationService
{
    /**
     * Prepare contact data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(
        array $data,
        string $contactableType,
        int $contactableId,
        int $createdBy
    ): array {
        return [
            'contactable_type' => $contactableType,
            'contactable_id' => $contactableId,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare contact data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string,mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
    {
        return array_filter([
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? null,
            'updated_by' => $updatedBy,
        ], fn (mixed $value): bool => $value !== null);
    }
}

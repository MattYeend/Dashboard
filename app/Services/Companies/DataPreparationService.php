<?php

namespace App\Services\Companies;

class DataPreparationService
{
    /**
     * Prepare company data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data, int $createdBy): array
    {
        return [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'website' => $data['website'] ?? null,
            'registration_number' => $data['registration_number'] ?? null,
            'vat_number' => $data['vat_number'] ?? null,
            'description' => $data['description'] ?? null,
            'industry_id' => $data['industry_id'] ?? null,
            'employee_count' => $data['employee_count'] ?? null,
            'founded_year' => $data['founded_year'] ?? null,
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare company data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
    {
        $allowed = [
            'name',
            'slug',
            'email',
            'phone',
            'website',
            'registration_number',
            'vat_number',
            'description',
            'industry_id',
            'employee_count',
            'founded_year',
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

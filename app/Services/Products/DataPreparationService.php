<?php

declare(strict_types=1);

namespace App\Services\Products;

class DataPreparationService
{
    /**
     * Prepare product data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'sku' => strtoupper(trim((string) $data['sku'])),
            'name' => trim((string) $data['name']),
            'description' => $data['description'] ?? null,
            'unit' => $data['unit'] ?? 'each',
            'unit_price' => $data['unit_price'] ?? 0,
            'cost_price' => $data['cost_price'] ?? null,
            'tax_rate' => $data['tax_rate'] ?? 20,
            'currency' => $data['currency'] ?? 'GBP',
            'is_active' => (bool) ($data['is_active'] ?? true),
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare product data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        $allowed = [
            'sku',
            'name',
            'description',
            'unit',
            'unit_price',
            'cost_price',
            'tax_rate',
            'currency',
            'is_active',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $payload[$field] = match ($field) {
                'sku' => strtoupper(trim((string) $data[$field])),
                'name' => trim((string) $data[$field]),
                'is_active' => (bool) $data[$field],
                default => $data[$field],
            };
        }

        return $payload;
    }
}

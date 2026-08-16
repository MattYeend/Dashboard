<?php

namespace App\Services\Deals;

class DataPreparationService
{
    /**
     * Prepare deal data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'pipeline_id' => $data['pipeline_id'] ?? null,
            'stage_id' => $data['stage_id'] ?? null,
            'status_id' => $data['status_id'] ?? null,
            'company_id' => $data['company_id'] ?? null,
            'invoice_id' => $data['invoice_id'] ?? null,
            'value' => $data['value'] ?? 0,
            'currency' => $data['currency'] ?? 'GBP',
            'probability' => $data['probability'] ?? 0,
            'expected_close_date' => $data['expected_close_date'] ?? null,
            'closed_at' => $data['closed_at'] ?? null,
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare deal data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        $allowed = [
            'title',
            'description',
            'pipeline_id',
            'stage_id',
            'status_id',
            'company_id',
            'invoice_id',
            'value',
            'currency',
            'probability',
            'expected_close_date',
            'closed_at',
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

<?php

namespace App\Services\Tasks;

class DataPreparationService
{
    /**
     * Prepare task data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data, int $createdBy): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'assigned_date' => $data['assigned_date'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'status_id' => $data['status_id'] ?? null,
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare task data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
    {
        $allowed = [
            'title',
            'description',
            'due_date',
            'assigned_date',
            'assigned_to',
            'status_id',
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

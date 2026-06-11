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
        return array_filter([
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'assigned_date' => $data['assigned_date'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'status_id' => $data['status_id'] ?? null,
            'meta' => $data['meta'] ?? null,
            'updated_by' => $updatedBy,
        ], fn (mixed $value): bool => $value !== null);
    }
}

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
    public function prepareForCreation(array $data): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'assigned_date' => $data['assigned_date'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'status_id' => $data['status_id'] ?? null,
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare task data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        return array_filter([
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'assigned_date' => $data['assigned_date'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'status_id' => $data['status_id'] ?? null,
            'meta' => $data['meta'] ?? null,
        ], fn (mixed $value): bool => $value !== null);
    }
}

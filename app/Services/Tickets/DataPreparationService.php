<?php

namespace App\Services\Tickets;

use Mews\Purifier\Facades\Purifier;

class DataPreparationService
{
    /**
     * Prepare ticket data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data, int $createdBy): array
    {
        return [
            'title' => $data['title'],
            'description' => isset($data['description']) ? Purifier::clean($data['description'], 'tickets') : null,
            'ticket_status_id' => $data['ticket_status_id'] ?? null,
            'ticket_priority_id' => $data['ticket_priority_id'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'resolved_at' => null,
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare ticket data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
    {
        $allowed = [
            'title',
            'description',
            'ticket_status_id',
            'ticket_priority_id',
            'assigned_to',
            'due_date',
            'resolved_at',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $payload[$field] = match ($field) {
                'description' => $data['description'] !== null
                    ? Purifier::clean($data['description'], 'tickets')
                    : null,
                default => $data[$field],
            };
        }

        $payload['updated_by'] = $updatedBy;

        return $payload;
    }
}

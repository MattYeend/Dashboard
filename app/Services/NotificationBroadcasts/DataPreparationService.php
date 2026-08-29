<?php

namespace App\Services\NotificationBroadcasts;

use Mews\Purifier\Facades\Purifier;

class DataPreparationService
{
    /**
     * Prepare notification broadcast data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        $audienceType = $data['audience_type'] ?? 'all';

        return [
            'title' => $data['title'],
            'body' => Purifier::clean($data['body']),
            'audience_type' => $audienceType,
            'audience_ids' => $audienceType === 'all' ? null : ($data['audience_ids'] ?? null),
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare notification broadcast data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        $allowed = [
            'title',
            'body',
            'audience_type',
            'audience_ids',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $field === 'body' ? Purifier::clean($data[$field]) : $data[$field];
            }
        }

        // No ids are relevant once the audience is "all".
        if (($payload['audience_type'] ?? null) === 'all') {
            $payload['audience_ids'] = null;
        }

        return $payload;
    }
}

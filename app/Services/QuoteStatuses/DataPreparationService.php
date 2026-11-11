<?php

declare(strict_types=1);

namespace App\Services\QuoteStatuses;

use App\Models\QuoteStatus;

class DataPreparationService
{
    /**
     * Prepare quote status data for creation.
     *
     * system_key is never copied, so a status created through the app
     * can never become a system status.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'title' => trim((string) $data['title']),
            'description' => $data['description'] ?? null,
            'background_colour' => strtolower((string) ($data['background_colour'] ?? '#ffffff')),
            'text_colour' => strtolower((string) ($data['text_colour'] ?? '#000000')),
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare quote status data for update.
     *
     * The title of a system status is dropped here even if it reaches the
     * service, because Super Admin bypasses policies through Gate::before
     * and the form request is not the only entry point.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, QuoteStatus $quoteStatus): array
    {
        $allowed = [
            'title',
            'description',
            'background_colour',
            'text_colour',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            if ($field === 'title' && $quoteStatus->isSystem()) {
                continue;
            }

            $payload[$field] = match ($field) {
                'title' => trim((string) $data[$field]),
                'background_colour', 'text_colour' => strtolower((string) $data[$field]),
                default => $data[$field],
            };
        }

        return $payload;
    }
}
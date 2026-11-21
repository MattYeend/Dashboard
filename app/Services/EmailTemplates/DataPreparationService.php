<?php

declare(strict_types=1);

namespace App\Services\EmailTemplates;

use Mews\Purifier\Facades\Purifier;

class DataPreparationService
{
    /**
     * Whitelist of attributes a request may change. System-managed fields
     * (organisation, audit columns, restore timestamps) are never accepted.
     *
     * @var array<int, string>
     */
    private const UPDATABLE = [
        'name',
        'subject',
        'preheader',
        'body_html',
        'body_text',
        'is_active',
        'meta',
    ];

    /**
     * Prepare email template data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'name' => trim((string) $data['name']),
            'subject' => trim((string) $data['subject']),
            'preheader' => $this->nullableTrim($data['preheader'] ?? null),
            'body_html' => $this->sanitiseHtml((string) $data['body_html']),
            'body_text' => $this->nullableTrim($data['body_text'] ?? null),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare email template data for update, using the same
     * normalisation rules as creation for the keys present.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        $payload = [];

        foreach (self::UPDATABLE as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $payload[$field] = $this->normalise($field, $data[$field]);
        }

        return $payload;
    }

    /**
     * Normalise a single field value.
     */
    private function normalise(string $field, mixed $value): mixed
    {
        return match ($field) {
            'name', 'subject' => trim((string) $value),
            'preheader', 'body_text' => $this->nullableTrim($value),
            'body_html' => $this->sanitiseHtml((string) $value),
            'is_active' => (bool) $value,
            default => $value,
        };
    }

    /**
     * Trim a value, converting empty strings and null to null.
     */
    private function nullableTrim(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Strip unsafe markup from stored HTML (stored XSS, ISO 27001 A.8.28).
     */
    private function sanitiseHtml(string $html): string
    {
        return Purifier::clean($html);
    }
}
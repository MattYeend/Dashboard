<?php

declare(strict_types=1);

namespace App\Services\EmailSuppressions;

use App\Models\EmailSuppression;

class DataPreparationService
{
    /**
     * Whitelist of attributes a request may change. System-managed fields
     * (campaign, contact, suppressed_at, organisation and audit columns)
     * are never accepted here.
     *
     * @var array<int, string>
     */
    private const UPDATABLE = [
        'email',
        'reason',
        'notes',
        'meta',
    ];

    /**
     * Prepare email suppression data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'email' => $this->email($data['email'] ?? null) ?? '',
            'reason' => $this->reason($data['reason'] ?? null),
            'notes' => $this->text($data['notes'] ?? null),
            'suppressed_at' => now(),
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare email suppression data for update, using the same
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
            'email' => $this->email($value) ?? '',
            'reason' => $this->reason($value),
            'notes' => $this->text($value),
            default => $value,
        };
    }

    /**
     * Trim, strip control characters and lower-case an email address so the
     * unique (organisation_id, email) index cannot be bypassed by casing.
     */
    private function email(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = trim((string) preg_replace('/[\x00-\x1F\x7F]/u', '', (string) $value));

        return $clean === '' ? null : mb_strtolower($clean);
    }

    /**
     * Normalise a reason, falling back to manual for anything outside the
     * allow-list.
     */
    private function reason(mixed $value): string
    {
        $reason = mb_strtolower(trim((string) $value));

        return in_array($reason, EmailSuppression::REASONS, true)
            ? $reason
            : EmailSuppression::REASON_MANUAL;
    }

    /**
     * Trim free text and convert empty strings to null.
     */
    private function text(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = trim((string) $value);

        return $clean === '' ? null : $clean;
    }
}
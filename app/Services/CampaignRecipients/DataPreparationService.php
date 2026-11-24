<?php

declare(strict_types=1);

namespace App\Services\CampaignRecipients;

use App\Models\CampaignRecipient;
use Illuminate\Support\Str;

class DataPreparationService
{
    /**
     * Whitelist of attributes a caller may change on an existing recipient.
     * System-managed fields (status, token, workflow timestamps, attempts,
     * organisation and audit columns) are never accepted here. The sending
     * pipeline issue adds explicit transition methods for those.
     *
     * @var array<int, string>
     */
    private const UPDATABLE = [
        'meta',
    ];

    /**
     * Prepare campaign recipient data for creation. New recipients always
     * start as pending with a freshly generated token.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'campaign_id' => (int) $data['campaign_id'],
            'contact_id' => $this->nullableInt($data['contact_id'] ?? null),
            'email' => mb_strtolower($this->singleLine($data['email'] ?? '') ?? ''),
            'name' => $this->singleLine($data['name'] ?? null),
            'status' => CampaignRecipient::STATUS_PENDING,
            'token' => Str::random(40),
            'attempts' => 0,
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare campaign recipient data for update, using only the
     * whitelisted keys that are present.
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

            $payload[$field] = $data[$field];
        }

        return $payload;
    }

    /**
     * Trim a value, strip control characters and convert empty strings to null.
     */
    private function singleLine(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = trim((string) preg_replace('/[\x00-\x1F\x7F]/u', '', (string) $value));

        return $clean === '' ? null : $clean;
    }

    /**
     * Cast a value to an integer, converting empty values to null.
     */
    private function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}

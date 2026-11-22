<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Models\CampaignStatus;
use Illuminate\Support\Carbon;

class DataPreparationService
{
    /**
     * Whitelist of attributes a request may change. System-managed fields
     * (status, workflow timestamps, counters, rendered content, organisation
     * and audit columns) are never accepted here.
     *
     * @var array<int, string>
     */
    private const UPDATABLE = [
        'name',
        'email_template_id',
        'campaign_segment_id',
        'owner_id',
        'subject',
        'from_name',
        'reply_to',
        'scheduled_at',
        'meta',
    ];

    /**
     * Prepare campaign data for creation. New campaigns always start as drafts.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'name' => $this->singleLine($data['name'] ?? '') ?? '',
            'email_template_id' => $this->nullableInt($data['email_template_id'] ?? null),
            'campaign_segment_id' => $this->nullableInt($data['campaign_segment_id'] ?? null),
            'owner_id' => $this->nullableInt($data['owner_id'] ?? null),
            'subject' => $this->singleLine($data['subject'] ?? null),
            'from_name' => $this->singleLine($data['from_name'] ?? null),
            'reply_to' => $this->singleLine($data['reply_to'] ?? null),
            'scheduled_at' => $this->parseDate($data['scheduled_at'] ?? null),
            'status_id' => CampaignStatus::idForKey(CampaignStatus::KEY_DRAFT),
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare campaign data for update, using the same normalisation rules
     * as creation for the keys present.
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
            'name' => $this->singleLine($value) ?? '',
            'email_template_id', 'campaign_segment_id', 'owner_id' => $this->nullableInt($value),
            'subject', 'from_name', 'reply_to' => $this->singleLine($value),
            'scheduled_at' => $this->parseDate($value),
            default => $value,
        };
    }

    /**
     * Trim a value, strip control characters (prevents email header
     * injection through subject, from_name and reply_to, ISO 27001 A.8.28)
     * and convert empty strings to null.
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

    /**
     * Parse a date value, converting empty values to null.
     */
    private function parseDate(mixed $value): ?Carbon
    {
        return $value === null || $value === '' ? null : Carbon::parse((string) $value);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\CampaignSegments;

use App\Support\CampaignSegments\CriteriaRules;

class DataPreparationService
{
    /**
     * Prepare campaign segment data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'name' => $this->cleanText($data['name'] ?? null) ?? '',
            'description' => $this->cleanText($data['description'] ?? null),
            'criteria' => $this->normaliseCriteria($data['criteria'] ?? null),
        ];
    }

    /**
     * Prepare campaign segment data for update.
     *
     * Only keys present in the input are returned, so omitted fields are
     * never cleared.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        $payload = [];

        if (array_key_exists('name', $data)) {
            $payload['name'] = $this->cleanText($data['name']) ?? '';
        }

        if (array_key_exists('description', $data)) {
            $payload['description'] = $this->cleanText($data['description']);
        }

        if (array_key_exists('criteria', $data)) {
            $payload['criteria'] = $this->normaliseCriteria($data['criteria']);
        }

        return $payload;
    }

    /**
     * Keep only the three known keys of each criterion, in a fixed order,
     * with the value cast to the type its field expects.
     *
     * @return array<int, array{field: string, operator: string, value: bool|int|null}>|null
     */
    private function normaliseCriteria(mixed $criteria): ?array
    {
        if (! is_array($criteria) || $criteria === []) {
            return null;
        }

        $normalised = [];

        foreach ($criteria as $criterion) {
            if (! is_array($criterion)) {
                continue;
            }

            $field = (string) ($criterion['field'] ?? '');

            $normalised[] = [
                'field' => $field,
                'operator' => (string) ($criterion['operator'] ?? ''),
                'value' => CriteriaRules::castValue($field, $criterion['value'] ?? null),
            ];
        }

        return $normalised === [] ? null : $normalised;
    }

    /**
     * Trim text, strip control characters and convert empty strings to null.
     */
    private function cleanText(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $clean = trim((string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value));

        return $clean === '' ? null : $clean;
    }
}

<?php

namespace App\Services\Activities;

use Mews\Purifier\Facades\Purifier;

class DataPreparationService
{
    public function __construct(
        private readonly ActivityableTypeRegistryService $registry,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data, string $activityableType, int $activityableId): array
    {
        return [
            'activityable_type' => $this->resolveActivityableType($activityableType),
            'activityable_id' => $activityableId,
            'type' => $data['type'],
            'description' => isset($data['description']) ? Purifier::clean($data['description']) : null,
            'meta' => $data['meta'] ?? null,
            'occurred_at' => $data['occurred_at'] ?? now(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        $allowed = ['description', 'meta'];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $field === 'description' && $data[$field] !== null
                    ? Purifier::clean($data[$field])
                    : $data[$field];
            }
        }

        return $payload;
    }

    /**
     * Convert the short type key submitted by the form (e.g. "user") into
     * the fully-qualified class name stored in activities.activityable_type
     * (e.g. "App\Models\User"). Falls back to throwing if it isn't a
     * recognised short key, in case a fully-qualified name is ever passed
     * through directly.
     */
    private function resolveActivityableType(string $activityableType): string
    {
        return $this->registry->modelClassForKey($activityableType)
            ?? throw new \InvalidArgumentException("Unrecognised activityable type: {$activityableType}");
    }
}

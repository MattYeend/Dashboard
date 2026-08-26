<?php

namespace App\Services\InteractionLogs;

use InvalidArgumentException;

class DataPreparationService
{
    public function __construct(
        private readonly InteractionLoggableTypeRegistryService $registryService,
    ) {}

    /**
     * Prepare interaction log data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(
        array $data,
        string $interactableType,
        int $interactableId,
    ): array {
        return [
            'interactable_type' => $this->resolveInteractableType($interactableType),
            'interactable_id' => $interactableId,
            'type' => $data['type'],
            'subject' => $data['subject'],
            'outcome' => $data['outcome'] ?? null,
            'notes' => $data['notes'] ?? null,
            'occurred_at' => $data['occurred_at'],
            'contact_id' => $data['contact_id'] ?? null,
        ];
    }

    /**
     * Prepare interaction log data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        $allowed = [
            'type',
            'subject',
            'outcome',
            'notes',
            'occurred_at',
            'contact_id',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        return $payload;
    }

    /**
     * Convert the short type key submitted by the form (e.g. "company") into
     * the fully qualified class name stored in interaction_logs.interactable_type
     * (e.g. "App\Models\Company").
     */
    private function resolveInteractableType(string $interactableType): string
    {
        return $this->registryService->modelClassForKey($interactableType)
            ?? throw new \InvalidArgumentException("Unrecognised interactable type: {$interactableType}");
    }
}

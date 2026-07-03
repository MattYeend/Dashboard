<?php

namespace App\Services\Orders;

class DataPreparationService
{
    public function __construct(
        private readonly OrderableTypeRegistryService $registry,
    ) {}

    /**
     * Prepare order data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(
        array $data,
        string $orderableType,
        int $orderableId,
        int $createdBy
    ): array {
        return [
            'orderable_type' => $this->resolveOrderableType($orderableType),
            'orderable_id' => $orderableId,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? null,
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare order data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string,mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
    {
        $allowed = [
            'orderable_type',
            'orderable_id',
            'phone',
            'email',
            'address',
            'city',
            'postal_code',
            'country',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $field === 'orderable_type'
                    ? $this->resolveOrderableType($data[$field])
                    : $data[$field];
            }
        }

        $payload['updated_by'] = $updatedBy;

        return $payload;
    }

    /**
     * Convert the short type key submitted by the form (e.g. "user") into
     * the fully-qualified class name stored in orders.orderable_type
     * (e.g. "App\Models\User"). Falls back to the raw value if it isn't a
     * recognised short key, in case a fully-qualified name is ever passed
     * through directly.
     */
    private function resolveOrderableType(string $orderableType): string
    {
        return $this->registry->modelClassForKey($orderableType)
            ?? $orderableType;
    }
}

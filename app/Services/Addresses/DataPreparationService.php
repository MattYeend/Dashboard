<?php

namespace App\Services\Addresses;

class DataPreparationService
{
    public function __construct(
        private readonly AddressableTypeRegistryService $registry,
    ) {}

    /**
     * Prepare address data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(
        array $data,
        string $addressableType,
        int $addressableId,
        int $createdBy
    ): array {
        return [
            'addressable_type' => $this->resolveAddressableType($addressableType),
            'addressable_id' => $addressableId,
            'address_line_one' => $data['address_line_one'],
            'address_line_two' => $data['address_line_two'] ?? null,
            'town' => $data['town'] ?? null,
            'city' => $data['city'],
            'county' => $data['county'] ?? null,
            'postcode' => $data['postcode'] ?? null,
            'country' => $data['country'],
            'is_primary' => $data['is_primary'] ?? false,
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare address data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
    {
        $allowed = [
            'addressable_type',
            'addressable_id',
            'address_line_one',
            'address_line_two',
            'town',
            'city',
            'county',
            'postcode',
            'country',
            'is_primary',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $field === 'addressable_type'
                    ? $this->resolveAddressableType($data[$field])
                    : $data[$field];
            }
        }

        $payload['updated_by'] = $updatedBy;

        return $payload;
    }

    /**
     * Convert the short type key submitted by the form (e.g. "user") into
     * the fully-qualified class name stored in addresses.addressable_type
     * (e.g. "App\Models\User"). Falls back to throwing if it isn't a
     * recognised short key, in case a fully-qualified name is ever passed
     * through directly.
     */
    private function resolveAddressableType(string $addressableType): string
    {
        return $this->registry->modelClassForKey($addressableType)
        ?? throw new \InvalidArgumentException("Unrecognised addressable type: {$addressableType}");
    }
}

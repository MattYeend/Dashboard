<?php

namespace App\Services\Addresses;

use App\Models\Address;

class FormatterService
{
    public function __construct(
        private readonly AddressableTypeRegistryService $registry,
    ) {}

    /**
     * Format a single address with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Address $address): array
    {
        return [
            'id' => $address->id,
            'contactable_type' => $address->addressable_type,
            'contactable_id' => $address->addressable_id,
            'address' => $address->address,
            'city' => $address->city,
            'postal_code' => $address->postal_code,
            'country' => $address->country,
            'meta' => $address->meta,
            'created_at' => $address->created_at,
            'updated_at' => $address->updated_at,
            'deleted_at' => $address->deleted_at,
            'restored_at' => $address->restored_at,
            'creator' => $address->creator ? ['id' => $address->creator->id, 'name' => $address->creator->name] : null,
            'updater' => $address->updater ? ['id' => $address->updater->id, 'name' => $address->updater->name] : null,
            'deleter' => $address->deleter ? ['id' => $address->deleter->id, 'name' => $address->deleter->name] : null,
            'restorer' => $address->restorer ? ['id' => $address->restorer->id, 'name' => $address->restorer->name] : null,
            'contactable_type_label' => $address->contactable_type
                ? ($this->registry->labelForModel($address->contactable_type) ?? class_basename($address->contactable_type))
                : null,
            'contactable_name' => $address->contactable
                ? ($address->contactable->name
                    ?? $address->contactable->title
                    ?? '#'.$address->contactable->id)
                : null,
            'contactable_type_key' => $this->registry->keyForModel($address->contactable_type),
        ];
    }
}

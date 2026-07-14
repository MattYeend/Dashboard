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
            'addressable_type' => $address->addressable_type,
            'addressable_id' => $address->addressable_id,
            'address_line_one' => $address->address_line_one,
            'address_line_two' => $address->address_line_two,
            'town' => $address->town,
            'city' => $address->city,
            'county' => $address->county,
            'postcode' => $address->postcode,
            'country' => $address->country,
            'is_primary' => $address->is_primary,
            'meta' => $address->meta,
            'created_at' => $address->created_at,
            'updated_at' => $address->updated_at,
            'deleted_at' => $address->deleted_at,
            'restored_at' => $address->restored_at,
            'creator' => $address->creator ? ['id' => $address->creator->id, 'name' => $address->creator->name] : null,
            'updater' => $address->updater ? ['id' => $address->updater->id, 'name' => $address->updater->name] : null,
            'deleter' => $address->deleter ? ['id' => $address->deleter->id, 'name' => $address->deleter->name] : null,
            'restorer' => $address->restorer ? ['id' => $address->restorer->id, 'name' => $address->restorer->name] : null,
            'addressable_type_label' => $address->addressable_type
                ? ($this->registry->labelForModel($address->addressable_type) ?? class_basename($address->addressable_type))
                : null,
            'addressable_name' => $address->addressable
                ? ($address->addressable->name
                    ?? $address->addressable->title
                    ?? '#'.$address->addressable->id)
                : null,
            'addressable_type_key' => $this->registry->keyForModel($address->addressable_type),
        ];
    }
}

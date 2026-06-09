<?php

namespace App\Services\Contacts;

use App\Models\Contact;

class FormatterService
{
    /**
     * Format a single contact with all data.
     *
     *
     * @return array<string, mixed>
     */
    public function format(Contact $contact): array
    {
        return [
            'id' => $contact->id,
            'contactable_type' => $contact->contactable_type,
            'contactable_id' => $contact->contactable_id,
            'phone' => $contact->phone,
            'email' => $contact->email,
            'address' => $contact->address,
            'city' => $contact->city,
            'postal_code' => $contact->postal_code,
            'country' => $contact->country,
            'meta' => $contact->meta,
            'created_at' => $contact->created_at,
            'updated_at' => $contact->updated_at,
            'deleted_at' => $contact->deleted_at,
            'restored_at' => $contact->restored_at,
            'updater' => $contact->updater ? ['id' => $contact->updater->id, 'name' => $contact->updater->name] : null,
            'deleter' => $contact->deleter ? ['id' => $contact->deleter->id, 'name' => $contact->deleter->name] : null,
            'restorer' => $contact->restorer ? ['id' => $contact->restorer->id, 'name' => $contact->restorer->name] : null,
        ];
    }
}

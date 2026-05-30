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
            'created_by' => $contact->created_by,
            'updated_by' => $contact->updated_by,
            'deleted_by' => $contact->deleted_by,
            'restored_by' => $contact->restored_by,
        ];
    }
}

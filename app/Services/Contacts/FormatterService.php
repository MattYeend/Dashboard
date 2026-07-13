<?php

namespace App\Services\Contacts;

use App\Models\Contact;

class FormatterService
{
    public function __construct(
        private readonly ContactableTypeRegistryService $registry,
    ) {}

    /**
     * Format a single contact with all data.
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
            'meta' => $contact->meta,
            'created_at' => $contact->created_at,
            'updated_at' => $contact->updated_at,
            'deleted_at' => $contact->deleted_at,
            'restored_at' => $contact->restored_at,
            'creator' => $contact->creator ? ['id' => $contact->creator->id, 'name' => $contact->creator->name] : null,
            'updater' => $contact->updater ? ['id' => $contact->updater->id, 'name' => $contact->updater->name] : null,
            'deleter' => $contact->deleter ? ['id' => $contact->deleter->id, 'name' => $contact->deleter->name] : null,
            'restorer' => $contact->restorer ? ['id' => $contact->restorer->id, 'name' => $contact->restorer->name] : null,
            'contactable_type_label' => $contact->contactable_type
                ? ($this->registry->labelForModel($contact->contactable_type) ?? class_basename($contact->contactable_type))
                : null,
            'contactable_name' => $contact->contactable
                ? ($contact->contactable->name
                    ?? $contact->contactable->title
                    ?? '#'.$contact->contactable->id)
                : null,
            'contactable_type_key' => $this->registry->keyForModel($contact->contactable_type),
        ];
    }
}

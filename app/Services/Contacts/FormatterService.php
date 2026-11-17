<?php

declare(strict_types=1);

namespace App\Services\Contacts;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Model;

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
            'tags' => $contact->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
            ])->all(),
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
            'contactable_name' => $this->contactableName($contact),
            'contactable_type_key' => $this->registry->keyForModel($contact->contactable_type),
        ];
    }

    /**
     * Resolve a display name for the contact's owner, falling back to its key.
     */
    private function contactableName(Contact $contact): ?string
    {
        /** @var Model|null $owner */
        $owner = $contact->contactable;

        if ($owner === null) {
            return null;
        }

        foreach (['name', 'title'] as $attribute) {
            $value = $owner->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return '#'.$owner->getKey();
    }
}

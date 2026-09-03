<?php

namespace App\Services\Organisations;

use App\Models\Organisation;

class FormatterService
{
    /**
     * Format a single organisation with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Organisation $organisation): array
    {
        return [
            'id' => $organisation->id,
            'name' => $organisation->name,
            'slug' => $organisation->slug,
            'meta' => $organisation->meta,
            'members_count' => $organisation->users_count ?? $organisation->users()->count(),
            'created_at' => $organisation->created_at,
            'updated_at' => $organisation->updated_at,
            'deleted_at' => $organisation->deleted_at,
            'restored_at' => $organisation->restored_at,
            'creator' => $organisation->creator ? ['id' => $organisation->creator->id, 'name' => $organisation->creator->name] : null,
            'updater' => $organisation->updater ? ['id' => $organisation->updater->id, 'name' => $organisation->updater->name] : null,
            'deleter' => $organisation->deleter ? ['id' => $organisation->deleter->id, 'name' => $organisation->deleter->name] : null,
            'restorer' => $organisation->restorer ? ['id' => $organisation->restorer->id, 'name' => $organisation->restorer->name] : null,
        ];
    }
}

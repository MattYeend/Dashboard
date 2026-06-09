<?php

namespace App\Services\Users;

use App\Models\User;

class FormatterService
{
    /**
     * Format a single user with all data.
     *
     * @return array<string, mixed>
     */
    public function format(User $user): array
    {
        $user->loadMissing(['creator', 'updater', 'deleter', 'restorer']);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'role' => $user->role,
            'meta' => $user->meta,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'deleted_at' => $user->deleted_at,
            'restored_at' => $user->restored_at,
            'creator' => $user->creator ? ['id' => $user->creator->id, 'name' => $user->creator->name] : null,
            'updater' => $user->updater ? ['id' => $user->updater->id, 'name' => $user->updater->name] : null,
            'deleter' => $user->deleter ? ['id' => $user->deleter->id, 'name' => $user->deleter->name] : null,
            'restorer' => $user->restorer ? ['id' => $user->restorer->id, 'name' => $user->restorer->name] : null,
        ];
    }
}

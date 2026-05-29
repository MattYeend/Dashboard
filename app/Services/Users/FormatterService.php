<?php

namespace App\Services\Users;

use App\Models\User;

class FormatterService
{
    /**
     * Format a single user with all data.
     *
     * @param  User $user
     *
     * @return array<string, mixed>
     */
    public function format(User $user): array
    {
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
            'created_by' => $user->created_by,
            'updated_by' => $user->updated_by,
            'deleted_by' => $user->deleted_by,
            'restored_by' => $user->restored_by,
        ];
    }
}

<?php

namespace App\Services\Profile;

use App\Models\User;

class FormatterService
{
    /**
     * Shape the user for the profile pages. Secrets, hashes, meta and
     * two-factor material are never included.
     *
     * @return array<string, mixed>
     */
    public function format(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'role' => $user->role,
            'locale' => $user->locale,
            'two_factor_enabled' => $user->two_factor_confirmed_at !== null,
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }
}

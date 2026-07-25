<?php

namespace App\Services\ApiTokens;

use App\Models\User;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\NewAccessToken;

class CreatorService
{
    /**
     * Create a new personal access token for the given user.
     *
     * @param  array<int, string>  $abilities
     */
    public function create(
        User $user,
        string $name,
        array $abilities,
        ?string $expiresAt = null
    ): NewAccessToken {
        return $user->createToken(
            name: $name,
            abilities: $abilities,
            expiresAt: $expiresAt ? Carbon::parse($expiresAt) : null,
        );
    }
}

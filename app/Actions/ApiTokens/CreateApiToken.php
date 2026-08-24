<?php

namespace App\Actions\ApiTokens;

use App\Models\User;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\NewAccessToken;

/**
 * Issues a brand new Sanctum personal access token for a user.
 *
 * This sits outside the generic CreateResource action because token
 * creation isn't a standard "fill and save" Eloquent operation. Sanctum
 * generates the plaintext token internally and hands it back exactly once,
 * wrapped in a NewAccessToken value object, so that one-time-reveal
 * behaviour needs its own dedicated action rather than being squeezed into
 * a shared resource-creation flow.
 */
class CreateApiToken
{
    /**
     * @param  array<int, string>  $abilities
     */
    public function handle(
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

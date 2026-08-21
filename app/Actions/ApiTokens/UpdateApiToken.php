<?php

namespace App\Actions\ApiTokens;

use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Updates a token's name, abilities, and/or expiry.
 *
 * Deliberately whitelists the three editable fields instead of accepting
 * a raw array and mass-assigning it. PersonalAccessToken is a vendor model
 * whose guarding we don't control, so an explicit fillable surface here is
 * the safer default regardless of what Sanctum's own guarding looks like.
 */
class UpdateApiToken
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(
        PersonalAccessToken $token, 
        array $data
    ): PersonalAccessToken {
        $token->fill([
            'name' => $data['name'] ?? $token->name,
            'abilities' => $data['abilities'] ?? $token->abilities,
            'expires_at' => array_key_exists('expires_at', $data)
                ? ($data['expires_at'] ? Carbon::parse($data['expires_at']) : null)
                : $token->expires_at,
        ])->save();

        return $token;
    }
}

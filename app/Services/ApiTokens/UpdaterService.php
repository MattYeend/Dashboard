<?php

namespace App\Services\ApiTokens;

use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

class UpdaterService
{
    /**
     * Update a token's name, abilities, and/or expiry.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(PersonalAccessToken $token, array $data): PersonalAccessToken
    {
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

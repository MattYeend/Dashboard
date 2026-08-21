<?php

namespace App\Actions\ApiTokens;

use Laravel\Sanctum\PersonalAccessToken;

/**
 * Revokes a Sanctum personal access token by deleting its row outright.
 *
 * Deletion is immediate: Sanctum authenticates by looking the hashed token
 * up in this table, so once the row is gone the token stops working on its
 * very next request. There's no separate "revoked" flag to manage.
 */
class DeleteApiToken
{
    public function handle(PersonalAccessToken $token): void
    {
        $token->delete();
    }
}

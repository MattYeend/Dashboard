<?php

namespace App\Services\ApiToken;

use Laravel\Sanctum\PersonalAccessToken;

class DeleterService
{
    /**
     * Revoke (delete) the given token.
     */
    public function delete(PersonalAccessToken $token): void
    {
        $token->delete();
    }
}

<?php

namespace App\Services\ApiTokens;

use App\Actions\ApiTokens\DeleteApiToken;
use App\Models\Log;
use Laravel\Sanctum\PersonalAccessToken;

class DeleterService
{
    public function __construct(
        private readonly DeleteApiToken $deleteApiToken,
    ) {}

    public function delete(PersonalAccessToken $token): void
    {
        $before = $token->only(['name', 'abilities']);
        $ownerId = $token->tokenable_id;

        $this->deleteApiToken->handle($token);

        Log::log(
            action: Log::ACTION_REVOKE_API_TOKEN,
            data: $before,
            relatedToUserId: $ownerId,
        );
    }
}

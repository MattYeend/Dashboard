<?php

namespace App\Services\ApiTokens;

use App\Actions\ApiTokens\CreateApiToken;
use App\Models\Log;
use App\Models\User;
use Laravel\Sanctum\NewAccessToken;

/**
 * Creates tokens on a user's behalf and logs the event.
 */
class CreatorService
{
    public function __construct(
        private readonly CreateApiToken $createApiToken,
    ) {}

    /**
     * @param  array<int, string>  $abilities
     */
    public function create(User $user, string $name, array $abilities, ?string $expiresAt = null): NewAccessToken
    {
        $newToken = $this->createApiToken->handle($user, $name, $abilities, $expiresAt);

        Log::log(
            action: Log::ACTION_CREATE_API_TOKEN,
            data: [
                'name' => $name,
                'abilities' => $abilities,
                'expires_at' => $expiresAt,
            ],
            relatedToUserId: $user->id,
        );

        return $newToken;
    }
}

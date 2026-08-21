<?php

namespace App\Services\ApiTokens;

use App\Actions\ApiTokens\UpdateApiToken;
use App\Models\Log;
use Laravel\Sanctum\PersonalAccessToken;

class UpdaterService
{
    public function __construct(
        private readonly UpdateApiToken $updateApiToken,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PersonalAccessToken $token, array $data): PersonalAccessToken
    {
        $before = $token->only(['name', 'abilities', 'expires_at']);

        $updated = $this->updateApiToken->handle($token, $data);

        Log::log(
            action: Log::ACTION_UPDATE_API_TOKEN,
            data: [
                'before' => $before,
                'after' => $updated->only(['name', 'abilities', 'expires_at']),
            ],
            relatedToUserId: $updated->tokenable_id,
        );

        return $updated;
    }
}

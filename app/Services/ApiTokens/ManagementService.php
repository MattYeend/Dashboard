<?php

namespace App\Services\ApiTokens;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

class ManagementService
{
    public function __construct(
        private readonly CreatorService $creatorService,
        private readonly UpdaterService $updaterService,
        private readonly DeleterService $deleterService,
        private readonly QueryService $queryService,
    ) {}

    /**
     * @param  array<int, string>  $abilities
     */
    public function create(
        User $user, 
        string $name, 
        array $abilities, 
        ?string $expiresAt = null
    ): NewAccessToken {
        return $this->creatorService->create($user, $name, $abilities, $expiresAt);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(
        PersonalAccessToken $token, 
        array $data
    ): PersonalAccessToken {
        return $this->updaterService->update($token, $data);
    }

    public function delete(PersonalAccessToken $token): void
    {
        $this->deleterService->delete($token);
    }

    /**
     * @return Collection<int, PersonalAccessToken>
     */
    public function forUser(User $user): Collection
    {
        return $this->queryService->forUser($user);
    }
}

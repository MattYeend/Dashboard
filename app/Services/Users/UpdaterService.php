<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly LogService $logService
    ) {}

    /**
     * Update an existing user.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        User $user,
        array $data,
        int $updatedBy
    ): User {
        $actor = User::findOrFail($updatedBy);

        return DB::transaction(function () use ($user, $data, $actor, $updatedBy) {
            $this->updateUser($user, $data, $updatedBy);

            $this->logService->logUpdate($user, $actor, $updatedBy);

            return $user->fresh();
        });
    }

    /**
     * Update user data.
     *
     * @param  array<string, mixed>  $data
     */
    protected function updateUser(User $user, array $data, int $updatedBy): void
    {
        $userData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        $user->update($userData);
    }
}

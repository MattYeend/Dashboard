<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     *
     * @param  DataPreparationService $dataPreparation
     * @param  LogService $logService
     */
    public function __construct(
        protected DataPreparationService $dataPreparation,
        protected LogService $logService
    ) {
    }

    /**
     * Update an existing user.
     *
     * @param  User $user
     * @param  array<string, mixed>  $data
     * @param  int $updatedBy
     *
     * @return User
     *
     * @throws \Exception
     */
    public function update(
        User $user,
        array $data,
        int $updatedBy
    ): User {
        return DB::transaction(function () use ($user, $data, $updatedBy) {
            $actor = User::findOrFail($updatedBy);

            $this->updateUser($user, $data);
            $this->logService->logUpdate($user, $actor, $updatedBy);

            return $user->fresh();
        });
    }

    /**
     * Update user data.
     *
     * @param  User $user
     * @param  array<string, mixed> $data
     *
     * @return void
     */
    protected function updateUser(User $user, array $data): void
    {
        $userData = $this->dataPreparation->prepareForUpdate($data);
        $user->update($userData);
    }
}

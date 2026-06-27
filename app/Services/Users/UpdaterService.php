<?php

namespace App\Services\Users;

use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
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

        $before = $this->auditLogService->snapshot($user);

        return DB::transaction(function () use ($user, $data, $actor, $updatedBy, $before) {
            $this->updateUser($user, $data, $updatedBy);

            $fresh = $user->fresh();

            $this->auditLogService->record(
                Log::ACTION_UPDATE_USER,
                $actor,
                $fresh,
                [
                    'before' => $before,
                    'after' => $this->auditLogService->snapshot($fresh),
                ],
                relatedUser: $fresh,
            );

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
        $displayRole = $userData['role'] ?? null;

        $user->update($userData);

        if ($displayRole !== null) {
            $user->assignApplicationRole($displayRole);
        }
    }
}

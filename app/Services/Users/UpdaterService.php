<?php

namespace App\Services\Users;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
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

        $userData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        return $this->updateResource->handle(
            $user,
            $userData,
            function (User $user) use ($actor, $before, $userData): void {
                if (isset($userData['role'])) {
                    $user->assignApplicationRole($userData['role']);
                }

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
            });
    }
}

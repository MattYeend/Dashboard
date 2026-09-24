<?php

namespace App\Services\Profile;

use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;

class PasswordUpdaterService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly SessionManagementService $sessionManagementService,
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Change the user's password and sign out every other session.
     * The password is hashed by the model cast and is never logged.
     */
    public function update(
        User $user, 
        string $password, 
        string $currentSessionId
    ): void {
        $user->forceFill([
            'password' => $password,
            'updated_by' => $user->id,
        ])->save();

        $this->sessionManagementService->revokeOthers($user, $currentSessionId);

        $this->auditLogService->record(
            Log::ACTION_PASSWORD_CHANGED, 
            $user, 
            $user, 
            []
        );
    }
}

<?php

namespace App\Services\Profile;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class DeleterService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly DeleteResource $deleteResource,
        private readonly SessionManagementService $sessionManagementService,
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Soft delete the user's own account, revoking tokens and sessions.
     * DeleteResource wraps this in its own transaction and calls
     * $model->delete() itself after the callback below runs.
     *
     * @throws ValidationException
     */
    public function delete(User $user): void
    {
        if ($this->isLastSuperAdmin($user)) {
            throw ValidationException::withMessages([
                'password' => 'You are the last super admin. Assign another super admin before deleting this account.',
            ]);
        }

        $before = $this->auditLogService->snapshot($user);

        $this->deleteResource->handle($user, function (Model $model) use ($user): void {
            $user->tokens()->delete();
            $this->sessionManagementService->revokeAll($user);

            $model->forceFill(['deleted_by' => $user->id])->save();
        });

        $this->auditLogService->record(
            Log::ACTION_PROFILE_DELETED, 
            $user, 
            $user, 
            [
                'before' => $before
            ]);
    }

    /**
     * Determine whether the user is the only remaining super admin.
     */
    private function isLastSuperAdmin(User $user): bool
    {
        if (! $user->hasRole('Super Admin')) {
            return false;
        }

        return ! User::query()
            ->whereKeyNot($user->id)
            ->whereHas('roles', fn ($query) => $query->where('name', 'Super Admin'))
            ->exists();
    }
}

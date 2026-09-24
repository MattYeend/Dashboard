<?php

namespace App\Services\Profile;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UpdaterService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly UpdateResource $updateResource,
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Update the user's own name and email. The role can never change here.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        $before = $this->auditLogService->snapshot($user);
        $emailChanged = strtolower($data['email']) !== strtolower($user->email);

        $payload = Arr::only($data, ['name', 'email']);

        $updated = $this->updateResource->handle($user, $payload, function (Model $model) use ($user, $emailChanged): void {
            $model->forceFill(['updated_by' => $user->id]);

            if ($emailChanged) {
                $model->forceFill(['email_verified_at' => null]);
            }

            $model->save();
        });

        /** @var User $updated */
        $this->auditLogService->record(
            Log::ACTION_PROFILE_UPDATED,
            $user,
            $updated,
            [
                'before' => $before, 'after' => $this->auditLogService->snapshot($updated)
            ],
        );

        if ($emailChanged) {
            $updated->sendEmailVerificationNotification();
        }

        return $updated;
    }
}

<?php

namespace App\Services\Users;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted user.
     *
     * @throws \Exception
     */
    public function restore(
        User $user,
        int $restoredBy,
        ?User $actor = null
    ): User {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $user,
            function (User $user) use ($actor, $restoredBy): void {
                $user->restored_by = $restoredBy;
                $user->restored_at = now();
                $user->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_USER,
                    $actor,
                    $user,
                    ['before' => $this->auditLogService->snapshot($user)],
                    relatedUser: $user,
                );
            });
    }

    /**
     * Restore multiple soft-deleted users.
     *
     * @return int Number of users restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $userIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($userIds, $restoredBy, &$count) {
            /** @var Collection<int,User> $users */
            $users = User::withTrashed()
                ->whereIn('id', $userIds)
                ->get();

            foreach ($users as $user) {
                if ($user->trashed()) {
                    $this->restore($user, $restoredBy);
                    $count++;
                }
            }
        });

        return $count;
    }
}

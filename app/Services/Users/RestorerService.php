<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the resorer service.
     *
     * @param LogService $logService
     */
    public function __construct(
        protected LogService $logService
    ) {
    }

    /**
     * Restore a soft-deleted user.
     *
     * @param  User $user
     * @param  int|null $restoredBy
     *
     * @return User
     *
     * @throws \Exception
     */
    public function restore(
        User $user,
        ?int $restoredBy = null
    ): User {
        return DB::transaction(function () use ($user, $restoredBy) {
            $actor = User::findOrFail($restoredBy);

            $user->restored_by = $restoredBy;
            $user->restored_at = now();
            $user->save();

            $user->restore();

            $this->logService->logRestoration($user, $actor, $restoredBy);

            return $user->fresh();
        });
    }

    /**
     * Restore multiple soft-deleted users.
     *
     * @param  array $userIds
     * @param  int|null $restoredBy
     *
     * @return int Number of users restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $userIds,
        ?int $restoredBy = null
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

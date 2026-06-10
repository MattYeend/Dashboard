<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected LogService $logService
    ) {}

    /**
     * Soft delete a user.
     *
     * @throws \Exception
     */
    public function delete(
        User $user,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return DB::transaction(function () use ($user, $deletedBy, $actor) {
            $user->deleted_by = $deletedBy;
            $user->save();

            $result = $user->delete();

            $this->logService->logDeletion($user, $actor, $deletedBy);

            return $result;
        });
    }

    /**
     * Force delete a user (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        User $user,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return DB::transaction(function () use ($user, $deletedBy, $actor) {
            $this->logService->logForceDeletion($user, $actor, $deletedBy);

            return $user->forceDelete();
        });
    }

    /**
     * Delete multiple users.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $userIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($userIds, $deletedBy, &$count) {
            $users = User::whereIn('id', $userIds)->get();

            foreach ($users as $user) {
                if ($this->delete($user, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

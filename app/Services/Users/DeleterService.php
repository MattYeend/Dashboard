<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     *
     * @param LogService $logService
     */
    public function __construct(
        protected LogService $logService
    ) {
    }

    /**
     * Soft delete a user.
     *
     * @param  User $user
     * @param  int|null $deletedBy
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function delete(
        User $user,
        ?int $deletedBy = null
    ): bool {
        return DB::transaction(function () use ($user, $deletedBy) {
            $actor = User::findOrFail($deletedBy);
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
     * @param  User $user
     * @param  int|null $deletedBy
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function forceDelete(
        User $user,
        ?int $deletedBy = null
    ): bool {
        return DB::transaction(function () use ($user, $deletedBy) {
            $actor = User::findOrFail($deletedBy);
            $this->logService->logForceDeletion($user, $actor, $deletedBy);

            return $user->forceDelete();
        });
    }

    /**
     * Delete multiple contacts.
     *
     * @param  array $contactIds
     * @param  int|null $deletedBy
     *
     * @return int
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $contactIds,
        ?int $deletedBy = null
    ): int {
        $count = 0;

        DB::transaction(function () use ($contactIds, $deletedBy, &$count) {
            $contacts = User::whereIn('id', $contactIds)->get();

            foreach ($contacts as $user) {
                if ($this->delete($user, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

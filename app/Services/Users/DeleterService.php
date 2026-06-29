<?php

namespace App\Services\Users;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
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

        return $this->deleteResource->handle(
            $user,
            function (User $user) use ($actor, $deletedBy): void {
                $user->deleted_by = $deletedBy;
                $user->deleted_at = now();
                $user->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_USER,
                    $actor,
                    $user,
                    ['before' => $user->toArray()],
                    relatedUser: $user,
                );
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

        return $this->deleteResource->forceHandle(
            $user,
            function (User $user) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_USER,
                    $actor,
                    $user,
                    ['before' => $user->toArray()],
                    relatedUser: $user,
                );
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

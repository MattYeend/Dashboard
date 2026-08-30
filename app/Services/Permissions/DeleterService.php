<?php

namespace App\Services\Permissions;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\Permission;
use App\Models\User;
use App\Services\AuditLogService;

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
     * Soft delete a permission.
     *
     * @throws \Exception
     */
    public function delete(
        Permission $permission,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $permission,
            function (Permission $permission) use ($actor, $deletedBy): void {
                $permission->deleted_by = $deletedBy;
                $permission->deleted_at = now();
                $permission->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_PERMISSION,
                    $actor,
                    $permission,
                    ['before' => $this->auditLogService->snapshot($permission)],
                );
            });
    }

    /**
     * Force delete a permission (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(Permission $permission, int $deletedBy): bool
    {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $permission,
            function (Permission $permission) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_PERMISSION,
                    $actor,
                    $permission,
                    ['before' => $this->auditLogService->snapshot($permission)],
                );
            });
    }
}

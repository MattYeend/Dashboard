<?php

namespace App\Services\Permissions;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\Permission;
use App\Models\User;
use App\Services\AuditLogService;

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
     * Restore a soft-deleted permission.
     *
     * @throws \Exception
     */
    public function restore(
        Permission $permission,
        int $restoredBy,
        ?User $actor = null
    ): Permission {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $permission,
            function (Permission $permission) use ($actor, $restoredBy): void {
                $permission->restored_by = $restoredBy;
                $permission->restored_at = now();
                $permission->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_PERMISSION,
                    $actor,
                    $permission,
                    ['before' => $this->auditLogService->snapshot($permission)],
                );
            });
    }
}

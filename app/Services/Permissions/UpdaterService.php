<?php

namespace App\Services\Permissions;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Permission;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update an existing permission.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Permission $permission,
        array $data,
        int $updatedBy
    ): Permission {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($permission);

        $permissionData = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $permission,
            $permissionData,
            function (Permission $permission) use ($actor, $before, $updatedBy): void {
                $permission->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
                $fresh = $permission->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_PERMISSION,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }

    /**
     * Sync the roles assigned to a permission and record the change.
     *
     * @param  array<int, int>  $roleIds
     *
     * @throws \Exception
     */
    public function assignRoles(
        Permission $permission,
        array $roleIds,
        int $updatedBy
    ): Permission {
        $actor = User::findOrFail($updatedBy);

        $before = ['role_ids' => $permission->roles()->pluck('roles.id')->all()];

        $permission->roles()->sync($roleIds);
        $permission->forceFill(['updated_by' => $updatedBy])->save();

        $fresh = $permission->fresh('roles');

        $this->auditLogService->record(
            Log::ACTION_ASSIGN_PERMISSION,
            $actor,
            $fresh,
            [
                'before' => $before,
                'after' => ['role_ids' => $fresh->roles->pluck('id')->all()],
            ],
        );

        return $fresh;
    }
}

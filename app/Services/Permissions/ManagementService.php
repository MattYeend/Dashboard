<?php

namespace App\Services\Permissions;

use App\Http\Requests\Permissions\AssignPermissionRolesRequest;
use App\Http\Requests\Permissions\StorePermissionRequest;
use App\Http\Requests\Permissions\UpdatePermissionRequest;
use App\Models\Log;
use App\Models\Permission;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $destructor,
        protected readonly RestorerService $restorer,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Create a new permission.
     */
    public function store(
        StorePermissionRequest $request
    ): Permission {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing permission.
     */
    public function update(
        UpdatePermissionRequest $request,
        Permission $permission
    ): Permission {
        return $this->updater->update(
            $permission,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Sync the roles assigned to a single permission.
     */
    public function assignRoles(
        AssignPermissionRolesRequest $request,
        Permission $permission
    ): Permission {
        return $this->updater->assignRoles(
            $permission,
            $request->validated()['role_ids'],
            $request->user()->id
        );
    }

    /**
     * Soft delete a permission.
     */
    public function destroy(
        Permission $permission,
        User $actor
    ): void {
        $this->destructor->delete($permission, $actor->id);
    }

    /**
     * Restore a soft-deleted permission.
     */
    public function restore(
        int $id,
        User $actor
    ): Permission {
        $permission = Permission::withTrashed()->findOrFail($id);

        return $this->restorer->restore($permission, $actor->id);
    }

    /**
     * Force delete a permission, permanently removing it from the database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $permission = Permission::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($permission, $actor->id);
    }

    /**
     * Bulk restore permissions.
     *
     * @param  array<int, int>  $ids
     * @return array<string, array<int, int>>
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $permissions = Permission::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($permissions as $permission) {
            /** @var Permission $permission */
            $authoriseCallback($permission);
            $this->restorer->restore($permission, $actor->id);
            $restored[] = $permission->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($permissions->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete permissions.
     *
     * @param  array<int, int>  $ids
     * @return array<string, array<int, int>>
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $permissions = Permission::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($permissions as $permission) {
            /** @var Permission $permission */
            $authoriseCallback($permission);
            $this->destructor->delete($permission, $actor->id);
            $deleted[] = $permission->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($permissions->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Sync the full permissions × roles assignment matrix in one transaction.
     *
     * @param  array<int, array{permission_id: int, role_ids: array<int, int>}>  $assignments
     */
    public function syncMatrix(array $assignments, User $actor): void
    {
        DB::transaction(function () use ($assignments, $actor): void {
            foreach ($assignments as $assignment) {
                $permission = Permission::findOrFail($assignment['permission_id']);

                $before = ['role_ids' => $permission->roles()->pluck('roles.id')->all()];

                $permission->roles()->sync($assignment['role_ids']);
                $permission->forceFill(['updated_by' => $actor->id])->save();

                $this->auditLogService->record(
                    Log::ACTION_ASSIGN_PERMISSION,
                    $actor,
                    $permission,
                    [
                        'before' => $before,
                        'after' => ['role_ids' => $assignment['role_ids']],
                    ],
                );
            }
        });
    }
}

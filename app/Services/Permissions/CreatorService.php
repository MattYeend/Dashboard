<?php

namespace App\Services\Permissions;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\Permission;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Create a new permission.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Permission
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Permission {
                $permissionData = $this->dataPreparation->prepareForCreation($data);

                $newPermission = Permission::create($permissionData);

                /** @var Permission $newPermission */
                $newPermission->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_PERMISSION,
                    $actor,
                    $newPermission,
                    ['after' => $this->auditLogService->snapshot($newPermission)],
                );

                return $newPermission;
            });
    }
}

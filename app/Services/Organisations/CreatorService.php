<?php

namespace App\Services\Organisations;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\Organisation;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\PermissionRegistrar;

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
     * Create a new organisation.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Organisation
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Organisation {
                $organisationData = $this->dataPreparation->prepareForCreation($data);

                $newOrganisation = Organisation::create($organisationData);

                $newOrganisation->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                // The creator automatically becomes a member so they can
                // immediately switch into the organisation they made.
                $newOrganisation->users()->attach($createdBy);

                $this->assignCreatorAsAdmin($newOrganisation, $actor);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_ORGANISATION,
                    $actor,
                    $newOrganisation,
                    ['after' => $this->auditLogService->snapshot($newOrganisation)],
                );

                return $newOrganisation;
            });
    }

    /**
     * Give the creator the Admin role within the organisation they just
     * made, so they aren't left a member with no permissions in it.
     *
     * Temporarily switches the active permissions team to the new
     * organisation to make the assignment, then restores whatever team
     * was active for the rest of the request.
     */
    protected function assignCreatorAsAdmin(Organisation $organisation, User $actor): void
    {
        $registrar = app(PermissionRegistrar::class);
        $previousTeamId = $registrar->getPermissionsTeamId();

        $actor->unsetRelation('roles')->unsetRelation('permissions');
        $rolesToAssign = $actor->getRoleNames()->all();

        $registrar->setPermissionsTeamId($organisation->id);
        $actor->unsetRelation('roles')->unsetRelation('permissions');

        if ($rolesToAssign !== []) {
            $actor->assignRole($rolesToAssign);
        }

        $registrar->setPermissionsTeamId($previousTeamId);
        $actor->unsetRelation('roles')->unsetRelation('permissions');
    }
}

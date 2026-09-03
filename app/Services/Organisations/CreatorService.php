<?php

namespace App\Services\Organisations;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\Organisation;
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

                $this->auditLogService->record(
                    Log::ACTION_CREATE_ORGANISATION,
                    $actor,
                    $newOrganisation,
                    ['after' => $this->auditLogService->snapshot($newOrganisation)],
                );

                return $newOrganisation;
            });
    }
}

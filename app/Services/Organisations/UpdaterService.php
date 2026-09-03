<?php

namespace App\Services\Organisations;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Organisation;
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
     * Update an existing organisation.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(Organisation $organisation, array $data, int $updatedBy): Organisation
    {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($organisation);

        $organisationData = $this->dataPreparation->prepareForUpdate($data, $organisation->id);

        return $this->updateResource->handle(
            $organisation,
            $organisationData,
            function (Organisation $organisation) use ($actor, $before, $updatedBy): void {
                $organisation->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
                $fresh = $organisation->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_ORGANISATION,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }
}

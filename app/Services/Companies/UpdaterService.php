<?php

namespace App\Services\Companies;

use App\Actions\UpdateResource;
use App\Models\Company;
use App\Models\Log;
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
     * Update an existing company.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Company $company,
        array $data,
        int $updatedBy
    ): Company {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($company);

        $taskData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        return $this->updateResource->handle(
            $company,
            $taskData,
            function (Company $company) use ($actor, $before, $updatedBy): void {
                $fresh = $company->fresh();

                $company->updated_by = $updatedBy;
                $company->updated_at = now();
                $company->save();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_COMPANY,
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

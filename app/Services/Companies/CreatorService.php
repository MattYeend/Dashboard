<?php

namespace App\Services\Companies;

use App\Actions\CreateResource;
use App\Models\Company;
use App\Models\Log;
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
     * Create a new company.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(
        array $data,
        int $createdBy
    ): Company {
        $actor = User::findOrFail(
            $createdBy
        );

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Company {
                $companyData = $this->dataPreparation->prepareForCreation(
                    $data,
                    $createdBy
                );

                $newCompany = Company::create($companyData);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_COMPANY,
                    $actor,
                    $newCompany,
                    [
                        'after' => $this->auditLogService->snapshot(
                            $newCompany
                        ),
                    ],
                );

                return $newCompany;
            }
        );
    }
}

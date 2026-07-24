<?php

namespace App\Services\Industries;

use App\Actions\CreateResource;
use App\Models\Industry;
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
     * Create a new industry.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(
        array $data,
        int $createdBy
    ): Industry {
        $actor = User::findOrFail(
            $createdBy
        );

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Industry {
                $industryData = $this->dataPreparation->prepareForCreation(
                    $data,
                    $createdBy
                );

                $newIndustry = Industry::create(
                    $industryData
                );

                $this->auditLogService->record(
                    Log::ACTION_CREATE_INDUSTRY,
                    $actor,
                    $newIndustry,
                    [
                        'after' => $this->auditLogService->snapshot(
                            $newIndustry
                        ),
                    ],
                );

                return $newIndustry;
            }
        );
    }
}

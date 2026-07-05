<?php

namespace App\Services\Industries;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\Industry;
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
    public function create(array $data, int $createdBy): Industry
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Industry {
                $taskData = $this->dataPreparation->prepareForCreation($data, $createdBy);

                $newIndustry = Industry::create($taskData);

                $newIndustry->created_by = $createdBy;
                $newIndustry->created_at = now();
                $newIndustry->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_INDUSTRY,
                    $actor,
                    $newIndustry,
                    ['after' => $newIndustry->toArray()],
                );

                return $newIndustry;
            });
    }

    /**
     * Create the industry record.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createIndustry(array $data, int $createdBy): Industry
    {
        $industryData = $this->dataPreparation->prepareForCreation($data, $createdBy);

        return Industry::create($industryData);
    }
}

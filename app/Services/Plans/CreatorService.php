<?php

namespace App\Services\Plans;

use App\Actions\CreateResource;
use App\Models\Plan;
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
     * Create a new plan.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Plan
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Plan {
                $taskData = $this->dataPreparation->prepareForCreation($data, $createdBy);

                $newIndustry = Plan::create($taskData);

                $newIndustry->created_by = $createdBy;
                $newIndustry->created_at = now();
                $newIndustry->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_PLAN,
                    $actor,
                    $newIndustry,
                    ['after' => $newIndustry->toArray()],
                );

                return $newIndustry;
            });
    }

    /**
     * Create the plan record.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createIndustry(array $data, int $createdBy): Plan
    {
        $industryData = $this->dataPreparation->prepareForCreation($data, $createdBy);

        return Plan::create($industryData);
    }
}

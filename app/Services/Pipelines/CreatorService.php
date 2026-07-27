<?php

namespace App\Services\Pipelines;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\Pipeline;
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
     * Create a new pipeline.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Pipeline
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Pipeline {
                $pipelineData = $this->dataPreparation->prepareForCreation($data, $createdBy);

                $newPipeline = Pipeline::create($pipelineData);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_PIPELINE,
                    $actor,
                    $newPipeline,
                    ['after' => $this->auditLogService->snapshot($newPipeline)],
                );

                return $newPipeline;
            });
    }
}

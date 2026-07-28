<?php

namespace App\Services\PipelineStages;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\PipelineStage;
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
     * Create a new pipeline stage.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $pipelineId, int $createdBy): PipelineStage
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($pipelineId, $createdBy, $actor): PipelineStage {
                $pipelineStageData = $this->dataPreparation->prepareForCreation($data, $pipelineId, $createdBy);

                $newPipelineStage = PipelineStage::create($pipelineStageData);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_PIPELINE_STAGE,
                    $actor,
                    $newPipelineStage,
                    ['after' => $this->auditLogService->snapshot($newPipelineStage)],
                );

                return $newPipelineStage;
            });
    }
}

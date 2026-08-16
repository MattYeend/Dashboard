<?php

namespace App\Services\PipelineStatuses;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\PipelineStatus;
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
     * Create a new pipelineStatus.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): PipelineStatus
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): PipelineStatus {
                $pipelineStatusData = $this->dataPreparation->prepareForCreation($data);

                $newPipelineStatus = PipelineStatus::create($pipelineStatusData);

                $newPipelineStatus->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_PIPELINE_STATUS,
                    $actor,
                    $newPipelineStatus,
                    ['after' => $this->auditLogService->snapshot($newPipelineStatus)],
                );

                return $newPipelineStatus;
            });
    }
}

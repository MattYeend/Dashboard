<?php

namespace App\Services\TaskStatuses;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\TaskStatus;
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
     * Create a new taskStatus.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): TaskStatus
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): TaskStatus {
                $taskStatusData = $this->dataPreparation->prepareForCreation($data, $createdBy);

                $newTaskStatus = TaskStatus::create($taskStatusData);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_TASK_STATUS,
                    $actor,
                    $newTaskStatus,
                    ['after' => $this->auditLogService->snapshot($newTaskStatus)],
                );

                return $newTaskStatus;
            });
    }
}

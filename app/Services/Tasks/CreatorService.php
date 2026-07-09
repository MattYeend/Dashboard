<?php

namespace App\Services\Tasks;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\Task;
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
     * Create a new task.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Task
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Task {
                $taskData = $this->dataPreparation->prepareForCreation($data, $createdBy);

                $newTask = Task::create($taskData);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_TASK,
                    $actor,
                    $newTask,
                    ['after' => $this->auditLogService->snapshot($newTask)],
                );

                return $newTask;
            });
    }
}

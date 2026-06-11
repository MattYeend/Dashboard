<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected DataPreparationService $dataPreparation,
        protected LogService $logService
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

        return DB::transaction(function () use ($data, $createdBy, $actor) {
            $task = $this->createTask($data, $createdBy);
            $this->logService->logCreation($task, $actor, $createdBy);

            return $task;
        });
    }

    /**
     * Create the task record.
     *
     * @param  array<string, mixed>  $data
     * @param  int  $createdBy
     */
    protected function createTask(array $data, int $createdBy): Task
    {
        $taskData = $this->dataPreparation->prepareForCreation($data, $createdBy);

        return Task::create($taskData);
    }
}

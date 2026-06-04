<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected DataPreparationService $dataPreparation,
        protected LogService $logService
    ) {}

    /**
     * Update an existing task.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(Task $task, array $data, int $updatedBy): Task
    {
        return DB::transaction(function () use ($task, $data, $updatedBy) {
            $actor = User::findOrFail($updatedBy);

            $this->updateTask($task, $data);
            $this->logService->logUpdate($task, $actor, $updatedBy);

            return $task->fresh();
        });
    }

    /**
     * Update task data.
     *
     * @param  array<string, mixed>  $data
     */
    protected function updateTask(Task $task, array $data): void
    {
        $taskData = $this->dataPreparation->prepareForUpdate($data);
        $task->update($taskData);
    }
}

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
        protected readonly DataPreparationService $dataPreparation,
        protected readonly LogService $logService
    ) {}

    /**
     * Update an existing task.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Task $task,
        array $data,
        int $updatedBy
    ): Task {
        $actor = User::findOrFail($updatedBy);

        return DB::transaction(function () use ($task, $data, $updatedBy, $actor) {
            $this->updateTask($task, $data, $updatedBy);
            $this->logService->logUpdate($task, $actor, $updatedBy);

            return $task->fresh();
        });
    }

    /**
     * Update task data.
     *
     * @param  array<string, mixed>  $data
     */
    protected function updateTask(Task $task, array $data, int $updatedBy): void
    {
        $taskData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);
        $task->update($taskData);
    }
}

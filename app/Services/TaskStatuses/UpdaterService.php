<?php

namespace App\Services\TaskStatuses;

use App\Models\TaskStatus;
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
     * Update an existing task status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        TaskStatus $taskStatus,
        array $data,
        int $updatedBy
    ): TaskStatus {
        $actor = User::findOrFail($updatedBy);

        return DB::transaction(function () use ($taskStatus, $data, $updatedBy, $actor) {
            $this->updateContact($taskStatus, $data, $updatedBy);
            $this->logService->logUpdate($taskStatus, $actor, $updatedBy);

            return $taskStatus->fresh();
        });
    }

    /**
     * Update task status data.
     *
     * @param  array<string, mixed>  $data
     */
    protected function updateContact(TaskStatus $taskStatus, array $data, int $updatedBy): void
    {
        $taskStatusData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);
        $taskStatus->update($taskStatusData);
    }
}

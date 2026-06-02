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
        protected DataPreparationService $dataPreparation,
        protected LogService $logService
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
        return DB::transaction(function () use ($taskStatus, $data, $updatedBy) {
            $actor = User::findOrFail($updatedBy);

            $this->updateContact($taskStatus, $data);
            $this->logService->logUpdate($taskStatus, $actor, $updatedBy);

            return $taskStatus->fresh();
        });
    }

    /**
     * Update task status data.
     *
     * @param  array<string, mixed>  $data
     */
    protected function updateContact(TaskStatus $taskStatus, array $data): void
    {
        $taskStatusData = $this->dataPreparation->prepareForUpdate($data);
        $taskStatus->update($taskStatusData);
    }
}

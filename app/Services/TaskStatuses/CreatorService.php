<?php

namespace App\Services\TaskStatuses;

use App\Models\TaskStatus;
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
     * Create a new taskStatus.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): TaskStatus
    {
        $actor = User::findOrFail($createdBy);

        return DB::transaction(function () use ($data, $createdBy, $actor) {
            $taskStatus = $this->createContact($data);
            $this->logService->logCreation($taskStatus, $actor, $createdBy);

            return $taskStatus;
        });
    }

    /**
     * Create the taskStatus record.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createContact(array $data): TaskStatus
    {
        $contactData = $this->dataPreparation->prepareForCreation(
            $data
        );

        return TaskStatus::create($contactData);
    }
}

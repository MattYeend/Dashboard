<?php

namespace App\Services\TaskStatuses;

use App\Models\Log;
use App\Models\TaskStatus;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService
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
            $taskStatus = $this->createContact($data, $createdBy);
            $this->auditLogService->record(
                Log::ACTION_CREATE_TASK_STATUS,
                $actor,
                $taskStatus,
                ['before' => $taskStatus->toArray()],
            );

            return $taskStatus;
        });
    }

    /**
     * Create the taskStatus record.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createContact(array $data, int $createdBy): TaskStatus
    {
        $contactData = $this->dataPreparation->prepareForCreation(
            $data, $createdBy
        );

        return TaskStatus::create($contactData);
    }
}

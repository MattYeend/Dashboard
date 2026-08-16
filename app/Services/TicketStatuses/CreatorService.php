<?php

namespace App\Services\TicketStatuses;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\TicketStatus;
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
     * Create a new ticketStatus.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): TicketStatus
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): TicketStatus {
                $ticketStatusData = $this->dataPreparation->prepareForCreation($data);

                $newTicketStatus = TicketStatus::create($ticketStatusData);

                $newTicketStatus->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_TICKET_STATUS,
                    $actor,
                    $newTicketStatus,
                    ['after' => $this->auditLogService->snapshot($newTicketStatus)],
                );

                return $newTicketStatus;
            });
    }
}

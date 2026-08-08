<?php

namespace App\Services\TicketPriorities;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\TicketPriority;
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
     * Create a new ticketPriority.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): TicketPriority
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): TicketPriority {
                $ticketPriorityData = $this->dataPreparation->prepareForCreation($data, $createdBy);

                $newTicketPriority = TicketPriority::create($ticketPriorityData);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_TICKET_PRIORITY,
                    $actor,
                    $newTicketPriority,
                    ['after' => $this->auditLogService->snapshot($newTicketPriority)],
                );

                return $newTicketPriority;
            });
    }
}

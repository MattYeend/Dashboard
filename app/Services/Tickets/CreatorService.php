<?php

namespace App\Services\Tickets;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\Ticket;
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
     * Create a new ticket.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Ticket
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Ticket {
                $ticketData = $this->dataPreparation->prepareForCreation($data);

                $newTicket = Ticket::create($ticketData);

                $newTicket->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $newTicket->labels()->sync($data['label_ids'] ?? []);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_TICKET,
                    $actor,
                    $newTicket,
                    ['after' => $this->auditLogService->snapshot($newTicket)],
                );

                return $newTicket;
            });
    }
}

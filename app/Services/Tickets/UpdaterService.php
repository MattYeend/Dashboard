<?php

namespace App\Services\Tickets;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update an existing ticket.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Ticket $ticket,
        array $data,
        int $updatedBy
    ): Ticket {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($ticket);

        $ticketData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        return $this->updateResource->handle(
            $ticket,
            $ticketData,
            function (Ticket $ticket) use ($actor, $before, $data): void {
                if (array_key_exists('label_ids', $data)) {
                    $ticket->labels()->sync($data['label_ids'] ?? []);
                }

                $fresh = $ticket->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_TICKET,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }
}

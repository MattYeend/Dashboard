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

        $ticketData = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $ticket,
            $ticketData,
            function (Ticket $ticket) use ($actor, $before, $data, $updatedBy): void {
                $ticket->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();

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

    /**
     * Mark a ticket as resolved, stamping resolved_at with the current time.
     *
     * @throws \Exception
     */
    public function resolve(
        Ticket $ticket,
        int $resolvedBy
    ): Ticket {
        $actor = User::findOrFail($resolvedBy);

        $before = $this->auditLogService->snapshot($ticket);

        return $this->updateResource->handle(
            $ticket,
            ['resolved_at' => now()],
            function (Ticket $ticket) use ($actor, $before, $resolvedBy): void {
                $ticket->forceFill([
                    'updated_by' => $resolvedBy,
                ])->save();

                $fresh = $ticket->fresh();

                $this->auditLogService->record(
                    Log::ACTION_RESOLVE_TICKET,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }

    /**
     * Mark a resolved ticket as unresolved, clearing resolved_at.
     *
     * @throws \Exception
     */
    public function unresolve(
        Ticket $ticket,
        int $unresolvedBy
    ): Ticket {
        $actor = User::findOrFail($unresolvedBy);

        $before = $this->auditLogService->snapshot($ticket);

        return $this->updateResource->handle(
            $ticket,
            ['resolved_at' => null],
            function (Ticket $ticket) use ($actor, $before, $unresolvedBy): void {
                $ticket->forceFill([
                    'updated_by' => $unresolvedBy,
                ])->save();

                $fresh = $ticket->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UNRESOLVE_TICKET,
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

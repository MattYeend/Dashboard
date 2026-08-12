<?php

namespace App\Services\Tickets;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete a ticket.
     *
     * @throws \Exception
     */
    public function delete(
        Ticket $ticket,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $ticket,
            function (Ticket $ticket) use ($actor, $deletedBy): void {
                $ticket->deleted_by = $deletedBy;
                $ticket->deleted_at = now();
                $ticket->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_TICKET,
                    $actor,
                    $ticket,
                    ['before' => $this->auditLogService->snapshot($ticket)],
                );
            });
    }

    /**
     * Force delete a ticket (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Ticket $ticket,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $ticket,
            function (Ticket $ticket) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_TICKET,
                    $actor,
                    $ticket,
                    ['before' => $this->auditLogService->snapshot($ticket)],
                );
            });
    }

    /**
     * Delete multiple tickets.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $ticketIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($ticketIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $tickets = Ticket::whereIn('id', $ticketIds)->get();

            foreach ($tickets as $ticket) {
                if ($this->delete($ticket, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

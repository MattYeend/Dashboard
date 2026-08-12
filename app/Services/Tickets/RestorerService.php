<?php

namespace App\Services\Tickets;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the resorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted ticket.
     *
     * @throws \Exception
     */
    public function restore(
        Ticket $ticket,
        int $restoredBy,
        ?User $actor = null
    ): Ticket {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $ticket,
            function (Ticket $ticket) use ($actor, $restoredBy): void {
                $ticket->restored_by = $restoredBy;
                $ticket->restored_at = now();
                $ticket->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_TICKET,
                    $actor,
                    $ticket,
                    ['before' => $this->auditLogService->snapshot($ticket)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted tickets.
     *
     * @return int Number of tickets restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $ticketIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($ticketIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,Ticket> $tickets */
            $tickets = Ticket::withTrashed()
                ->whereIn('id', $ticketIds)
                ->get();

            foreach ($tickets as $ticket) {
                if ($ticket->trashed()) {
                    $this->restore($ticket, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}

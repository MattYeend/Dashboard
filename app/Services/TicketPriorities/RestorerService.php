<?php

namespace App\Services\TicketPriorities;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\TicketPriority;
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
     * Restore a soft-deleted ticketPriority.
     *
     * @throws \Exception
     */
    public function restore(
        TicketPriority $ticketPriority,
        int $restoredBy,
        ?User $actor = null,
    ): TicketPriority {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $ticketPriority,
            function (TicketPriority $ticketPriority) use ($actor, $restoredBy): void {
                $ticketPriority->restored_by = $restoredBy;
                $ticketPriority->restored_at = now();
                $ticketPriority->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_TICKET_PRIORITY,
                    $actor,
                    $ticketPriority,
                    ['before' => $this->auditLogService->snapshot($ticketPriority)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted ticketPriorities.
     *
     * @return int Number of ticketPriorities restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $ticketPriorityIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($ticketPriorityIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,TicketPriority> $ticketPriorities */
            $ticketPriorities = TicketPriority::withTrashed()
                ->whereIn('id', $ticketPriorityIds)
                ->get();

            foreach ($ticketPriorities as $ticketPriority) {
                if ($ticketPriority->trashed()) {
                    $this->restore($ticketPriority, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}

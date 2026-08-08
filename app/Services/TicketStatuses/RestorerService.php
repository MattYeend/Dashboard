<?php

namespace App\Services\TicketStatuses;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\TicketStatus;
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
     * Restore a soft-deleted ticketStatus.
     *
     * @throws \Exception
     */
    public function restore(
        TicketStatus $ticketStatus,
        int $restoredBy,
        ?User $actor = null,
    ): TicketStatus {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $ticketStatus,
            function (TicketStatus $ticketStatus) use ($actor, $restoredBy): void {
                $ticketStatus->restored_by = $restoredBy;
                $ticketStatus->restored_at = now();
                $ticketStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_TICKET_STATUS,
                    $actor,
                    $ticketStatus,
                    ['before' => $this->auditLogService->snapshot($ticketStatus)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted ticketStatuses.
     *
     * @return int Number of ticketStatuses restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $ticketStatusIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($ticketStatusIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,TicketStatus> $ticketStatuses */
            $ticketStatuses = TicketStatus::withTrashed()
                ->whereIn('id', $ticketStatusIds)
                ->get();

            foreach ($ticketStatuses as $ticketStatus) {
                if ($ticketStatus->trashed()) {
                    $this->restore($ticketStatus, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}

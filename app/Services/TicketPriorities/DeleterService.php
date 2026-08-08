<?php

namespace App\Services\TicketPriorities;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\TicketPriority;
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
     * Soft delete a ticketPriority.
     *
     * @throws \Exception
     */
    public function delete(
        TicketPriority $ticketPriority,
        int $deletedBy,
        ?User $actor = null
    ): bool {

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $ticketPriority,
            function (TicketPriority $ticketPriority) use ($actor, $deletedBy): void {
                $ticketPriority->deleted_by = $deletedBy;
                $ticketPriority->deleted_at = now();
                $ticketPriority->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_TICKET_PRIORITY,
                    $actor,
                    $ticketPriority,
                    ['before' => $this->auditLogService->snapshot($ticketPriority)],
                );
            });
    }

    /**
     * Force delete a ticketPriority (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        TicketPriority $ticketPriority,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $ticketPriority,
            function (TicketPriority $ticketPriority) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_TICKET_PRIORITY,
                    $actor,
                    $ticketPriority,
                    ['before' => $this->auditLogService->snapshot($ticketPriority)],
                );
            });
    }

    /**
     * Delete multiple ticketPriorities.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $ticketPriorityIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($ticketPriorityIds, $deletedBy, &$count) {
            $ticketPriorities = TicketPriority::whereIn('id', $ticketPriorityIds)->get();

            foreach ($ticketPriorities as $ticketPriority) {
                if ($this->delete($ticketPriority, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

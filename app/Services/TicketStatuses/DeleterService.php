<?php

declare(strict_types=1);

namespace App\Services\TicketStatuses;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\TicketStatus;
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
     * Soft delete a ticket status.
     *
     * @throws \DomainException When the status is a protected system status.
     * @throws \Exception
     */
    public function delete(
        TicketStatus $ticketStatus,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $this->ensureNotSystem($ticketStatus);

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $ticketStatus,
            function (TicketStatus $ticketStatus) use ($actor, $deletedBy): void {
                $ticketStatus->deleted_by = $deletedBy;
                $ticketStatus->deleted_at = now();
                $ticketStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_TICKET_STATUS,
                    $actor,
                    $ticketStatus,
                    ['before' => $this->auditLogService->snapshot($ticketStatus)],
                );
            });
    }

    /**
     * Force delete a ticket status (permanent deletion).
     *
     * @throws \DomainException When the status is a protected system status.
     * @throws \Exception
     */
    public function forceDelete(
        TicketStatus $ticketStatus,
        int $deletedBy
    ): bool {
        $this->ensureNotSystem($ticketStatus);

        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $ticketStatus,
            function (TicketStatus $ticketStatus) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_TICKET_STATUS,
                    $actor,
                    $ticketStatus,
                    ['before' => $this->auditLogService->snapshot($ticketStatus)],
                );
            });
    }

    /**
     * Delete multiple ticket statuses.
     *
     * System statuses are skipped rather than rejected, so one protected row
     * cannot roll back the whole batch. The returned count excludes them.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $ticketStatusIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($ticketStatusIds, $deletedBy, &$count) {
            $ticketStatuses = TicketStatus::whereIn('id', $ticketStatusIds)->get();

            foreach ($ticketStatuses as $ticketStatus) {
                if ($ticketStatus->isSystem()) {
                    continue;
                }

                if ($this->delete($ticketStatus, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }

    /**
     * Refuse to delete a protected system status.
     *
     * @throws \DomainException
     */
    protected function ensureNotSystem(TicketStatus $ticketStatus): void
    {
        if ($ticketStatus->isSystem()) {
            throw new \DomainException('System statuses cannot be deleted.');
        }
    }
}

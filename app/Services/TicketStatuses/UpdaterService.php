<?php

declare(strict_types=1);

namespace App\Services\TicketStatuses;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\TicketStatus;
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
     * Update an existing ticket status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \DomainException When a system status would be renamed.
     * @throws \Exception
     */
    public function update(
        TicketStatus $ticketStatus,
        array $data,
        int $updatedBy
    ): TicketStatus {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($ticketStatus);

        $ticketStatusData = $this->dataPreparation->prepareForUpdate($data);

        $this->ensureNotRenamed($ticketStatus, $ticketStatusData);

        return $this->updateResource->handle(
            $ticketStatus,
            $ticketStatusData,
            function (TicketStatus $ticketStatus) use ($actor, $before, $updatedBy): void {
                $ticketStatus->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
                $fresh = $ticketStatus->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_TICKET_STATUS,
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
     * Refuse to rename a protected system status.
     *
     * The title is part of the workflow contract for system statuses. An
     * unchanged title is allowed so edit forms that always send it still work.
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws \DomainException
     */
    protected function ensureNotRenamed(TicketStatus $ticketStatus, array $payload): void
    {
        if (
            $ticketStatus->isSystem()
            && array_key_exists('title', $payload)
            && $payload['title'] !== $ticketStatus->title
        ) {
            throw new \DomainException('System statuses cannot be renamed.');
        }
    }
}

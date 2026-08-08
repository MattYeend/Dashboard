<?php

namespace App\Services\TicketStatuses;

use App\Http\Requests\TicketStatuses\ImportTicketStatusRequest;
use App\Http\Requests\TicketStatuses\StoreTicketStatusRequest;
use App\Http\Requests\TicketStatuses\UpdateTicketStatusRequest;
use App\Models\TicketStatus;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $destructor,
        protected readonly RestorerService $restorer,
        protected readonly ImporterService $importer,
        protected readonly ExporterService $exporter,
    ) {}

    /**
     * Create a new ticket status.
     */
    public function store(
        StoreTicketStatusRequest $request
    ): TicketStatus {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing ticket status.
     */
    public function update(
        UpdateTicketStatusRequest $request,
        TicketStatus $ticketStatus
    ): TicketStatus {
        return $this->updater->update(
            $ticketStatus,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a ticket status.
     */
    public function destroy(
        TicketStatus $ticketStatus,
        User $actor
    ): void {
        $this->destructor->delete($ticketStatus, $actor->id);
    }

    /**
     * Restore a soft-deleted ticket status.
     */
    public function restore(
        int $id,
        User $actor
    ): TicketStatus {
        $ticketStatus = TicketStatus::withTrashed()->findOrFail($id);

        return $this->restorer->restore($ticketStatus, $actor->id);
    }

    /**
     * Force delete a ticket status, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $ticketStatus = TicketStatus::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($ticketStatus, $actor->id);
    }

    /**
     * Bulk restore ticket statuses.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $ticketStatuses = TicketStatus::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($ticketStatuses as $ticketStatus) {
            /** @var TicketStatus $ticketStatus */
            $authoriseCallback($ticketStatus);
            $this->restorer->restore($ticketStatus, $actor->id);
            $restored[] = $ticketStatus->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($ticketStatuses->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete ticket statuses.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $ticketStatuses = TicketStatus::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($ticketStatuses as $ticketStatus) {
            /** @var TicketStatus $ticketStatus */
            $authoriseCallback($ticketStatus);
            $this->destructor->delete($ticketStatus, $actor->id);
            $deleted[] = $ticketStatus->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($ticketStatuses->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import ticket statuses from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportTicketStatusRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export ticket statuses matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }
}

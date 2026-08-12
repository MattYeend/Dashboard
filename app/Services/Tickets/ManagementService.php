<?php

namespace App\Services\Tickets;

use App\Http\Requests\Tickets\ImportTicketRequest;
use App\Http\Requests\Tickets\StoreTicketRequest;
use App\Http\Requests\Tickets\UpdateTicketRequest;
use App\Models\Ticket;
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
     * Create a new ticket.
     */
    public function store(
        StoreTicketRequest $request
    ): Ticket {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing ticket.
     */
    public function update(
        UpdateTicketRequest $request,
        Ticket $ticket
    ): Ticket {
        return $this->updater->update(
            $ticket,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a ticket.
     */
    public function destroy(
        Ticket $ticket,
        User $actor
    ): void {
        $this->destructor->delete($ticket, $actor->id);
    }

    /**
     * Restore a soft-deleted ticket.
     */
    public function restore(
        int $id,
        User $actor
    ): Ticket {
        $ticket = Ticket::withTrashed()->findOrFail($id);

        return $this->restorer->restore($ticket, $actor->id);
    }

    /**
     * Force delete a ticket, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $ticket = Ticket::withTrashed()->findOrFail($id);

        $this->destructor->forceDelete($ticket, $actor->id);
    }

    /**
     * Bulk restore tickets.
     *
     * @param  array<int, int>  $ids
     * @return array{restored: array<int, int>, skipped: array<int, int>}
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $tickets = Ticket::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($tickets as $ticket) {
            /** @var Ticket $ticket */
            $authoriseCallback($ticket);

            $this->restorer->restore($ticket, $actor->id);

            $restored[] = $ticket->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($tickets->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete tickets.
     *
     * @param  array<int, int>  $ids
     * @return array{deleted: array<int, int>, skipped: array<int, int>}
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $tickets = Ticket::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($tickets as $ticket) {
            /** @var Ticket $ticket */
            $authoriseCallback($ticket);

            $this->destructor->delete($ticket, $actor->id);

            $deleted[] = $ticket->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($tickets->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import tickets from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportTicketRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export tickets matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }
}

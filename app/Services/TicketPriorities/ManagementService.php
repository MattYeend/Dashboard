<?php

namespace App\Services\TicketPriorities;

use App\Http\Requests\TicketPriorities\ImportTicketPriorityRequest;
use App\Http\Requests\TicketPriorities\StoreTicketPriorityRequest;
use App\Http\Requests\TicketPriorities\UpdateTicketPriorityRequest;
use App\Models\TicketPriority;
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
     * Create a new ticket priority.
     */
    public function store(
        StoreTicketPriorityRequest $request
    ): TicketPriority {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing ticket priority.
     */
    public function update(
        UpdateTicketPriorityRequest $request,
        TicketPriority $ticketPriority
    ): TicketPriority {
        return $this->updater->update(
            $ticketPriority,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a ticket priority.
     */
    public function destroy(
        TicketPriority $ticketPriority,
        User $actor
    ): void {
        $this->destructor->delete($ticketPriority, $actor->id);
    }

    /**
     * Restore a soft-deleted ticket priority.
     */
    public function restore(
        int $id,
        User $actor
    ): TicketPriority {
        $ticketPriority = TicketPriority::withTrashed()->findOrFail($id);

        return $this->restorer->restore($ticketPriority, $actor->id);
    }

    /**
     * Force delete a ticket priority, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $ticketPriority = TicketPriority::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($ticketPriority, $actor->id);
    }

    /**
     * Bulk restore ticket priorities.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $ticketPriorities = TicketPriority::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($ticketPriorities as $ticketPriority) {
            /** @var TicketPriority $ticketPriority */
            $authoriseCallback($ticketPriority);
            $this->restorer->restore($ticketPriority, $actor->id);
            $restored[] = $ticketPriority->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($ticketPriorities->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete ticket priorities.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $ticketPriorities = TicketPriority::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($ticketPriorities as $ticketPriority) {
            /** @var TicketPriority $ticketPriority */
            $authoriseCallback($ticketPriority);
            $this->destructor->delete($ticketPriority, $actor->id);
            $deleted[] = $ticketPriority->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($ticketPriorities->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import ticket priorities from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportTicketPriorityRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export ticket priorities matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }
}

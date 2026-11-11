<?php

declare(strict_types=1);

namespace App\Services\QuoteStatuses;

use App\Http\Requests\QuoteStatuses\ImportQuoteStatusRequest;
use App\Http\Requests\QuoteStatuses\StoreQuoteStatusRequest;
use App\Http\Requests\QuoteStatuses\UpdateQuoteStatusRequest;
use App\Models\QuoteStatus;
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
     * Create a new quote status.
     */
    public function store(StoreQuoteStatusRequest $request): QuoteStatus
    {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing quote status.
     */
    public function update(UpdateQuoteStatusRequest $request, QuoteStatus $quoteStatus): QuoteStatus
    {
        return $this->updater->update(
            $quoteStatus,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a quote status.
     */
    public function destroy(QuoteStatus $quoteStatus, User $actor): void
    {
        $this->destructor->delete($quoteStatus, $actor->id);
    }

    /**
     * Restore a soft-deleted quote status.
     */
    public function restore(int $id, User $actor): QuoteStatus
    {
        $quoteStatus = QuoteStatus::onlyTrashed()->findOrFail($id);

        return $this->restorer->restore($quoteStatus, $actor->id);
    }

    /**
     * Force delete a quote status, permanently removing it from the database.
     */
    public function forceDelete(int $id, User $actor): void
    {
        $quoteStatus = QuoteStatus::onlyTrashed()->findOrFail($id);

        $this->destructor->forceDelete($quoteStatus, $actor->id);
    }

    /**
     * Bulk restore quote statuses.
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

        $quoteStatusesById = QuoteStatus::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get()
            ->keyBy('id');

        $restored = [];
        $skipped = [];

        foreach ($requestedIds as $id) {
            $quoteStatus = $quoteStatusesById->get($id);

            if ($quoteStatus === null) {
                $skipped[] = $id;

                continue;
            }

            $authoriseCallback($quoteStatus);

            $this->restorer->restore($quoteStatus, $actor->id);

            $restored[] = $quoteStatus->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $skipped,
        ];
    }

    /**
     * Bulk soft delete quote statuses.
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

        $quoteStatusesById = QuoteStatus::whereIn('id', $requestedIds)->get()->keyBy('id');

        $deleted = [];
        $skipped = [];

        foreach ($requestedIds as $id) {
            $quoteStatus = $quoteStatusesById->get($id);

            if ($quoteStatus === null) {
                $skipped[] = $id;

                continue;
            }

            $authoriseCallback($quoteStatus);

            $this->destructor->delete($quoteStatus, $actor->id);

            $deleted[] = $quoteStatus->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $skipped,
        ];
    }

    /**
     * Import quote statuses from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(ImportQuoteStatusRequest $request): array
    {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export quote statuses matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }

    /**
     * Expose the importer service for controller-level preview/commit actions.
     */
    public function importer(): ImporterService
    {
        return $this->importer;
    }
}

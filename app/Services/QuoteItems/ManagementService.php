<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Http\Requests\QuoteItems\ImportQuoteItemRequest;
use App\Http\Requests\QuoteItems\StoreQuoteItemRequest;
use App\Http\Requests\QuoteItems\UpdateQuoteItemRequest;
use App\Models\Quote;
use App\Models\QuoteItem;
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
     * Add a line to a quote.
     */
    public function store(StoreQuoteItemRequest $request, Quote $quote): QuoteItem
    {
        return $this->creator->create(
            $request->validated(),
            $quote->id,
            $request->user()->id
        );
    }

    /**
     * Update an existing line.
     */
    public function update(UpdateQuoteItemRequest $request, QuoteItem $item): QuoteItem
    {
        return $this->updater->update(
            $item,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a line.
     */
    public function destroy(QuoteItem $item, User $actor): void
    {
        $this->destructor->delete($item, $actor->id);
    }

    /**
     * Restore a soft-deleted line of the given quote.
     */
    public function restore(Quote $quote, int $id, User $actor): QuoteItem
    {
        $item = QuoteItem::onlyTrashed()->where('quote_id', $quote->id)->findOrFail($id);

        return $this->restorer->restore($item, $actor->id);
    }

    /**
     * Force delete a trashed line of the given quote.
     */
    public function forceDelete(Quote $quote, int $id, User $actor): void
    {
        $item = QuoteItem::onlyTrashed()->where('quote_id', $quote->id)->findOrFail($id);

        $this->destructor->forceDelete($item, $actor->id);
    }

    /**
     * Bulk soft delete lines of the given quote.
     *
     * @param  array<int, int>  $ids
     * @return array{deleted: array<int, int>, skipped: array<int, int>}
     */
    public function bulkDelete(Quote $quote, array $ids, User $actor, callable $authoriseCallback): array
    {
        $requestedIds = collect($ids)->unique()->values();

        $itemsById = QuoteItem::query()
            ->where('quote_id', $quote->id)
            ->whereIn('id', $requestedIds)
            ->get()
            ->keyBy('id');

        $deleted = [];
        $skipped = [];

        foreach ($requestedIds as $id) {
            $item = $itemsById->get($id);

            if ($item === null) {
                $skipped[] = $id;

                continue;
            }

            $authoriseCallback($item);

            $this->destructor->delete($item, $actor->id, $actor);

            $deleted[] = $item->id;
        }

        return ['deleted' => $deleted, 'skipped' => $skipped];
    }

    /**
     * Bulk restore lines of the given quote.
     *
     * @param  array<int, int>  $ids
     * @return array{restored: array<int, int>, skipped: array<int, int>}
     */
    public function bulkRestore(Quote $quote, array $ids, User $actor, callable $authoriseCallback): array
    {
        $requestedIds = collect($ids)->unique()->values();

        $itemsById = QuoteItem::onlyTrashed()
            ->where('quote_id', $quote->id)
            ->whereIn('id', $requestedIds)
            ->get()
            ->keyBy('id');

        $restored = [];
        $skipped = [];

        foreach ($requestedIds as $id) {
            $item = $itemsById->get($id);

            if ($item === null) {
                $skipped[] = $id;

                continue;
            }

            $authoriseCallback($item);

            $this->restorer->restore($item, $actor->id, $actor);

            $restored[] = $item->id;
        }

        return ['restored' => $restored, 'skipped' => $skipped];
    }

    /**
     * Import lines from an uploaded file into a quote.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(ImportQuoteItemRequest $request, Quote $quote): array
    {
        return $this->importer->forQuote($quote)->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export a quote's lines matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters, Quote $quote): StreamedResponse
    {
        return $this->exporter->export($filters, $quote);
    }

    /**
     * Expose the importer, scoped to a quote, for preview/commit actions.
     */
    public function importer(Quote $quote): ImporterService
    {
        return $this->importer->forQuote($quote);
    }
}

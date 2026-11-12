<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Actions\Quotes\ConvertQuoteToOrder;
use App\Http\Requests\Quotes\ImportQuoteRequest;
use App\Http\Requests\Quotes\StoreQuoteRequest;
use App\Http\Requests\Quotes\UpdateQuoteRequest;
use App\Models\Order;
use App\Models\Quote;
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
        protected readonly TransitionService $transitions,
        protected readonly ConvertQuoteToOrder $converter,
    ) {}

    /**
     * Create a new quote.
     */
    public function store(StoreQuoteRequest $request): Quote
    {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing quote.
     */
    public function update(UpdateQuoteRequest $request, Quote $quote): Quote
    {
        return $this->updater->update(
            $quote,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a quote.
     */
    public function destroy(Quote $quote, User $actor): void
    {
        $this->destructor->delete($quote, $actor->id);
    }

    /**
     * Restore a soft-deleted quote.
     */
    public function restore(int $id, User $actor): Quote
    {
        $quote = Quote::onlyTrashed()->findOrFail($id);

        return $this->restorer->restore($quote, $actor->id);
    }

    /**
     * Force delete a quote, permanently removing it from the database.
     */
    public function forceDelete(int $id, User $actor): void
    {
        $quote = Quote::onlyTrashed()->findOrFail($id);

        $this->destructor->forceDelete($quote, $actor->id);
    }

    /**
     * Bulk restore quotes.
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

        $quotesById = Quote::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get()
            ->keyBy('id');

        $restored = [];
        $skipped = [];

        foreach ($requestedIds as $id) {
            $quote = $quotesById->get($id);

            if ($quote === null) {
                $skipped[] = $id;

                continue;
            }

            $authoriseCallback($quote);

            $this->restorer->restore($quote, $actor->id);

            $restored[] = $quote->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $skipped,
        ];
    }

    /**
     * Bulk soft delete quotes.
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

        $quotesById = Quote::whereIn('id', $requestedIds)->get()->keyBy('id');

        $deleted = [];
        $skipped = [];

        foreach ($requestedIds as $id) {
            $quote = $quotesById->get($id);

            if ($quote === null) {
                $skipped[] = $id;

                continue;
            }

            $authoriseCallback($quote);

            $this->destructor->delete($quote, $actor->id);

            $deleted[] = $quote->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $skipped,
        ];
    }

    /**
     * Import quotes from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(ImportQuoteRequest $request): array
    {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export quotes matching the given filters as a CSV download.
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

    /**
     * Send a draft quote.
     */
    public function send(Quote $quote, User $actor): Quote
    {
        return $this->transitions->send($quote, $actor);
    }

    /**
     * Record that the customer accepted a sent quote.
     */
    public function accept(Quote $quote, User $actor): Quote
    {
        return $this->transitions->accept($quote, $actor);
    }

    /**
     * Record that the customer declined a sent quote.
     */
    public function decline(Quote $quote, User $actor): Quote
    {
        return $this->transitions->decline($quote, $actor);
    }

    /**
     * Convert an accepted quote into an order.
     */
    public function convert(Quote $quote, User $actor): Order
    {
        return $this->converter->handle($quote, $actor);
    }
}

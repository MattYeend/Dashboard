<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkIdsRequest;
use App\Http\Requests\QuoteItems\ImportQuoteItemRequest;
use App\Http\Requests\QuoteItems\StoreQuoteItemRequest;
use App\Http\Requests\QuoteItems\UpdateQuoteItemRequest;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Services\QuoteItems\ManagementService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuoteItemController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        private readonly ManagementService $management,
    ) {}

    /**
     * Add a line to the quote.
     *
     * Validation and authorisation are handled by StoreQuoteItemRequest.
     */
    public function store(
        StoreQuoteItemRequest $request,
        Quote $quote
    ): JsonResponse|RedirectResponse {
        $item = $this->management->store($request, $quote);

        if ($request->wantsJson()) {
            return response()->json($item, 201);
        }

        return redirect()->route('quotes.show', $quote->id);
    }

    /**
     * Update a line of the quote.
     *
     * Validation and authorisation are handled by UpdateQuoteItemRequest.
     */
    public function update(
        UpdateQuoteItemRequest $request,
        Quote $quote,
        QuoteItem $quoteItem
    ): JsonResponse|RedirectResponse {
        $item = $this->management->update($request, $quoteItem);

        if ($request->wantsJson()) {
            return response()->json($item);
        }

        return redirect()->route('quotes.show', $quote->id);
    }

    /**
     * Remove a line from the quote.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(
        Request $request,
        Quote $quote,
        QuoteItem $quoteItem
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $quoteItem);

        $this->management->destroy($quoteItem, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('quotes.show', $quote->id);
    }

    /**
     * Restore a soft-deleted line of the quote.
     *
     * Route model binding excludes trashed records, so the line is resolved
     * manually and only within this quote.
     */
    public function restore(
        int $id,
        Request $request,
        Quote $quote
    ): JsonResponse|RedirectResponse {
        $item = QuoteItem::onlyTrashed()->where('quote_id', $quote->id)->findOrFail($id);

        $this->authorize('restore', $item);

        $this->management->restore($quote, $id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('quotes.show', $quote->id);
    }

    /**
     * Permanently delete a soft-deleted line of the quote.
     */
    public function forceDelete(
        int $id,
        Request $request,
        Quote $quote
    ): JsonResponse|RedirectResponse {
        $item = QuoteItem::onlyTrashed()->where('quote_id', $quote->id)->findOrFail($id);

        $this->authorize('forceDelete', $item);

        $this->management->forceDelete($quote, $id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('quotes.show', $quote->id);
    }

    /**
     * Bulk soft-delete lines of the quote. Each line is authorised.
     */
    public function bulkDelete(BulkIdsRequest $request, Quote $quote): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', QuoteItem::class);

        $result = $this->management->bulkDelete(
            $quote,
            $request->validated('ids'),
            $request->user(),
            fn (QuoteItem $item) => $this->authorize('delete', $item)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('quotes.show', $quote->id);
    }

    /**
     * Bulk restore lines of the quote. Each line is authorised.
     */
    public function bulkRestore(BulkIdsRequest $request, Quote $quote): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', QuoteItem::class);

        $result = $this->management->bulkRestore(
            $quote,
            $request->validated('ids'),
            $request->user(),
            fn (QuoteItem $item) => $this->authorize('restore', $item)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('quotes.show', $quote->id);
    }

    /**
     * Import lines from an uploaded CSV file into the quote.
     *
     * Authorisation is handled by ImportQuoteItemRequest::authorize().
     */
    public function import(ImportQuoteItemRequest $request, Quote $quote): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request, $quote);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('quotes.show', $quote->id)
            ->with('import_result', $result);
    }

    /**
     * Export the quote's lines as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request, Quote $quote): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', [QuoteItem::class, $quote]);

        return $this->management->export(
            $request->only(['search', 'trashed']),
            $quote
        );
    }

    /**
     * Parse and validate an uploaded CSV against the quote, returning a
     * per-row preview without persisting anything.
     */
    public function importPreview(ImportQuoteItemRequest $request, Quote $quote): JsonResponse
    {
        $importer = $this->management->importer($quote);
        $token = $importer->storeUpload($request->file('file'), $request->user()->id);

        return response()->json(['token' => $token, ...$importer->preview($token, $request->user()->id)]);
    }

    /**
     * Commit a previously previewed import into the quote.
     */
    public function importCommit(Request $request, Quote $quote): JsonResponse
    {
        $this->authorize('import', [QuoteItem::class, $quote]);

        $request->validate(['token' => ['required', 'uuid']]);

        return response()->json(
            $this->management->importer($quote)->commit($request->input('token'), $request->user()->id)
        );
    }
}

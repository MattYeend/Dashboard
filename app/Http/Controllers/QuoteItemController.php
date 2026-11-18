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
use App\Services\QuoteItems\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuoteItemController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        private readonly QueryService $query,
        private readonly ManagementService $management,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Quote $quote, Request $request): Response
    {
        $this->authorize('viewAny', QuoteItem::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $quote,
            $request->only([
                'search',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render('QuoteItems/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(Quote $quote): Response
    {
        $this->authorize('create', QuoteItem::class);

        return Inertia::render('QuoteItems/Create', $this->query->getFormData($quote));
    }

    /**
     * Store a newly created resource in storage.
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

        return redirect()->route('quotes.items.show', [$quote->id, $item->id]);
    }

    /**
     * Display the specified resource.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(Quote $quote, QuoteItem $quoteItem, Request $request): Response
    {
        $this->authorize('view', $quoteItem);

        $data = $this->query->getById($request->user(), $quote, $quoteItem);

        return Inertia::render('QuoteItems/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(Quote $quote, QuoteItem $quoteItem, Request $request): Response
    {
        $this->authorize('update', $quoteItem);

        $data = array_merge(
            $this->query->getById($request->user(), $quote, $quoteItem),
            $this->query->getFormData($quote)
        );

        return Inertia::render('QuoteItems/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
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

        return redirect()->route('quotes.items.show', [$quote->id, $item->id]);
    }

    /**
     * Remove the specified resource from storage.
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

        return redirect()->route('quotes.items.index', $quote->id);
    }

    /**
     * Restore a soft-deleted quote item.
     *
     * Route model binding excludes trashed records, so the item is resolved
     * manually through the parent quote.
     */
    public function restore(
        Quote $quote,
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $item = $quote->items()->onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $item);

        $this->management->restore($quote, $id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('quotes.items.index', $quote->id);
    }

    /**
     * Permanently delete a soft-deleted quote item.
     */
    public function forceDelete(
        Quote $quote,
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $item = $quote->items()->onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $item);

        $this->management->forceDelete($quote, $id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('quotes.items.index', $quote->id);
    }

    /**
     * Bulk soft-delete quote items. Each item is authorised individually.
     */
    public function bulkDelete(
        Quote $quote,
        BulkIdsRequest $request
    ): JsonResponse|RedirectResponse {
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

        return redirect()->route('quotes.items.index', $quote->id);
    }

    /**
     * Bulk restore quote items. Each item is authorised individually.
     */
    public function bulkRestore(
        Quote $quote,
        BulkIdsRequest $request
    ): JsonResponse|RedirectResponse {
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

        return redirect()->route('quotes.items.index', $quote->id);
    }

    /**
     * Import quote items from an uploaded CSV file.
     *
     * Authorisation is handled by ImportQuoteItemRequest::authorize().
     */
    public function import(
        ImportQuoteItemRequest $request,
        Quote $quote
    ): JsonResponse|RedirectResponse {
        $result = $this->management->import($request, $quote);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('quotes.items.index', $quote->id)
            ->with('import_result', $result);
    }

    /**
     * Export the quote's items as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(
        Quote $quote,
        Request $request
    ): JsonResponse|RedirectResponse|StreamedResponse {
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

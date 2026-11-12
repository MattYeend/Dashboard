<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkIdsRequest;
use App\Http\Requests\Quotes\ImportQuoteRequest;
use App\Http\Requests\Quotes\StoreQuoteRequest;
use App\Http\Requests\Quotes\UpdateQuoteRequest;
use App\Models\Quote;
use App\Services\Quotes\ManagementService;
use App\Services\Quotes\QueryService;
use App\Services\Quotes\QuotableTypeRegistryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuoteController extends Controller
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
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Quote::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'status_id',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render('Quotes/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Quote::class);

        return Inertia::render('Quotes/Create', $this->query->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation and authorisation are handled by StoreQuoteRequest.
     */
    public function store(
        StoreQuoteRequest $request
    ): JsonResponse|RedirectResponse {
        $quote = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($quote, 201);
        }

        return redirect()->route('quotes.show', $quote->id);
    }

    /**
     * Display the specified resource.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(Quote $quote, Request $request): Response
    {
        $this->authorize('view', $quote);

        return Inertia::render(
            'Quotes/Show',
            $this->query->getById($request->user(), $quote)
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(Quote $quote, Request $request): Response
    {
        $this->authorize('update', $quote);

        return Inertia::render(
            'Quotes/Edit',
            $this->query->getById($request->user(), $quote)
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation and authorisation are handled by UpdateQuoteRequest.
     */
    public function update(
        UpdateQuoteRequest $request,
        Quote $quote
    ): JsonResponse|RedirectResponse {
        $quote = $this->management->update($request, $quote);

        if ($request->wantsJson()) {
            return response()->json($quote);
        }

        return redirect()->route('quotes.show', $quote->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(
        Request $request,
        Quote $quote
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $quote);

        $this->management->destroy($quote, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('quotes.index');
    }

    /**
     * Restore a soft-deleted quote.
     *
     * Route model binding excludes trashed records, so the model is
     * resolved manually. Authorises via the 'restore' policy.
     */
    public function restore(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $quote = Quote::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $quote);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('quotes.index');
    }

    /**
     * Permanently delete a soft-deleted quote.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $quote = Quote::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $quote);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('quotes.index');
    }

    /**
     * Bulk soft-delete multiple quotes.
     *
     * Authorises each quote individually via the 'delete' policy.
     */
    public function bulkDelete(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', Quote::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $request->user(),
            fn (Quote $quote) => $this->authorize('delete', $quote)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('quotes.index');
    }

    /**
     * Bulk restore multiple soft-deleted quotes.
     *
     * Authorises each quote individually via the 'restore' policy.
     */
    public function bulkRestore(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', Quote::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $request->user(),
            fn (Quote $quote) => $this->authorize('restore', $quote)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('quotes.index');
    }

    /**
     * Import quotes from an uploaded CSV file.
     *
     * Authorisation is handled by ImportQuoteRequest::authorize().
     */
    public function import(ImportQuoteRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('quotes.index')
            ->with('import_result', $result);
    }

    /**
     * Export quotes matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', Quote::class);

        return $this->management->export(
            $request->only(['search', 'status_id', 'trashed'])
        );
    }

    /**
     * Parse and validate an uploaded CSV, returning a per-row preview
     * without persisting anything.
     *
     * Authorisation is handled by ImportQuoteRequest::authorize().
     */
    public function importPreview(ImportQuoteRequest $request): JsonResponse
    {
        $importer = $this->management->importer();
        $token = $importer->storeUpload($request->file('file'), $request->user()->id);

        return response()->json(['token' => $token, ...$importer->preview($token, $request->user()->id)]);
    }

    /**
     * Commit a previously previewed import, persisting valid rows only.
     *
     * Authorises via the 'import' policy before proceeding.
     */
    public function importCommit(Request $request): JsonResponse
    {
        $this->authorize('import', Quote::class);

        $request->validate(['token' => ['required', 'uuid']]);

        return response()->json($this->management->importer()->commit($request->input('token'), $request->user()->id));
    }

    /**
     * Return the customer options for a type, for the dependent dropdown.
     *
     * Authorises via the 'create' policy, which the form that uses it needs.
     */
    public function quotableOptions(string $type): JsonResponse
    {
        $this->authorize('create', Quote::class);

        abort_unless(
            app(QuotableTypeRegistryService::class)
                ->modelClassForKey($type) !== null,
            404
        );

        return response()->json(
            $this->query->getQuotableOptions($type)
        );
    }

    /**
     * Send a draft quote.
     */
    public function send(Request $request, Quote $quote): JsonResponse|RedirectResponse
    {
        $this->authorize('send', $quote);

        $quote = $this->management->send($quote, $request->user());

        return $this->transitionResponse($request, $quote);
    }

    /**
     * Record that the customer accepted the quote.
     */
    public function accept(Request $request, Quote $quote): JsonResponse|RedirectResponse
    {
        $this->authorize('accept', $quote);

        $quote = $this->management->accept($quote, $request->user());

        return $this->transitionResponse($request, $quote);
    }

    /**
     * Record that the customer declined the quote.
     */
    public function decline(Request $request, Quote $quote): JsonResponse|RedirectResponse
    {
        $this->authorize('decline', $quote);

        $quote = $this->management->decline($quote, $request->user());

        return $this->transitionResponse($request, $quote);
    }

    /**
     * Convert an accepted quote into an order.
     */
    public function convert(Request $request, Quote $quote): JsonResponse|RedirectResponse
    {
        $this->authorize('convert', $quote);

        $order = $this->management->convert($quote, $request->user());

        if ($request->wantsJson()) {
            return response()->json(['order_id' => $order->id], 201);
        }

        return redirect()->route('orders.show', $order->id);
    }

    /**
     * Build the response for a status transition.
     */
    private function transitionResponse(Request $request, Quote $quote): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['id' => $quote->id]);
        }

        return redirect()->route('quotes.show', $quote->id);
    }
}

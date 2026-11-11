<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkIdsRequest;
use App\Http\Requests\QuoteStatuses\ImportQuoteStatusRequest;
use App\Http\Requests\QuoteStatuses\StoreQuoteStatusRequest;
use App\Http\Requests\QuoteStatuses\UpdateQuoteStatusRequest;
use App\Models\QuoteStatus;
use App\Services\QuoteStatuses\ManagementService;
use App\Services\QuoteStatuses\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuoteStatusController extends Controller
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
     * Passes paginated quote statuses to the QuoteStatuses/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', QuoteStatus::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render('QuoteStatuses/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', QuoteStatus::class);

        return Inertia::render('QuoteStatuses/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreQuoteStatusRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(
        StoreQuoteStatusRequest $request
    ): JsonResponse|RedirectResponse {
        $quoteStatus = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($quoteStatus, 201);
        }

        return redirect()->route('quote-statuses.show', $quoteStatus->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single quote status to the QuoteStatuses/Show Inertia page.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        QuoteStatus $quoteStatus,
        Request $request
    ): Response {
        $this->authorize('view', $quoteStatus);

        $data = $this->query->getById(
            $request->user(),
            $quoteStatus
        );

        return Inertia::render('QuoteStatuses/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(QuoteStatus $quoteStatus, Request $request): Response
    {
        $this->authorize('update', $quoteStatus);

        $data = $this->query->getById(
            $request->user(),
            $quoteStatus
        );

        return Inertia::render('QuoteStatuses/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateQuoteStatusRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateQuoteStatusRequest $request,
        QuoteStatus $quoteStatus
    ): JsonResponse|RedirectResponse {
        $quoteStatus = $this->management->update(
            $request,
            $quoteStatus
        );

        if ($request->wantsJson()) {
            return response()->json($quoteStatus);
        }

        return redirect()->route('quote-statuses.show', $quoteStatus->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding. System statuses
     * are rejected by the policy, and again by DeleterService for Super
     * Admin, who bypasses policies through Gate::before.
     *
     * The audit log entry is written before the deletion so that the
     * quote status instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        QuoteStatus $quoteStatus
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $quoteStatus);

        $this->management->destroy(
            $quoteStatus,
            $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('quote-statuses.index');
    }

    /**
     * Restore a soft-deleted quote status.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'restore' policy before proceeding.
     */
    public function restore(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $quoteStatus = QuoteStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $quoteStatus);

        $this->management->restore(
            $id,
            $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('quote-statuses.index');
    }

    /**
     * Permanently delete a soft-deleted quote status.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $quoteStatus = QuoteStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $quoteStatus);

        $this->management->forceDelete(
            $id,
            $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('quote-statuses.index');
    }

    /**
     * Bulk soft-delete multiple quote statuses.
     *
     * Authorises each quote status individually via the 'delete' policy.
     */
    public function bulkDelete(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', QuoteStatus::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $request->user(),
            fn (QuoteStatus $quoteStatus) => $this->authorize('delete', $quoteStatus)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('quote-statuses.index');
    }

    /**
     * Bulk restore multiple soft-deleted quote statuses.
     *
     * Authorises each quote status individually via the 'restore' policy.
     */
    public function bulkRestore(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', QuoteStatus::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $request->user(),
            fn (QuoteStatus $quoteStatus) => $this->authorize('restore', $quoteStatus)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('quote-statuses.index');
    }

    /**
     * Import quote statuses from an uploaded CSV file.
     *
     * Authorisation is handled by ImportQuoteStatusRequest::authorize().
     */
    public function import(ImportQuoteStatusRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('quote-statuses.index')
            ->with('import_result', $result);
    }

    /**
     * Export quote statuses matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', QuoteStatus::class);

        return $this->management->export(
            $request->only(['search', 'trashed'])
        );
    }

    /**
     * Parse and validate an uploaded CSV, returning a per-row preview
     * without persisting anything.
     *
     * Authorisation is handled by ImportQuoteStatusRequest::authorize().
     */
    public function importPreview(ImportQuoteStatusRequest $request): JsonResponse
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
        $this->authorize('import', QuoteStatus::class);

        $request->validate(['token' => ['required', 'uuid']]);

        return response()->json($this->management->importer()->commit($request->input('token'), $request->user()->id));
    }
}

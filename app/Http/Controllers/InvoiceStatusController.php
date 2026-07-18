<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceStatuses\StoreInvoiceStatusRequest;
use App\Http\Requests\InvoiceStatuses\UpdateInvoiceStatusRequest;
use App\Models\InvoiceStatus;
use App\Services\InvoiceStatuses\ManagementService;
use App\Services\InvoiceStatuses\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceStatusController extends Controller
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
     * Passes paginated invoice statuses to the InvoiceStatuses/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', InvoiceStatus::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only(['search', 'sort_by', 'sort_direction', 'trashed', 'per_page'])
        );

        return Inertia::render('InvoiceStatuses/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', InvoiceStatus::class);

        return Inertia::render('InvoiceStatuses/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreInvoiceStatusRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(
        StoreInvoiceStatusRequest $request
    ): JsonResponse|RedirectResponse {
        $invoiceStatus = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($invoiceStatus, 201);
        }

        return redirect()->route('invoice-statuses.index', $invoiceStatus->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single invoiceStatus to the InvoiceStatuses/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        InvoiceStatus $invoiceStatus,
        Request $request
    ): Response {
        $this->authorize('view', $invoiceStatus);

        $data = $this->query->getById(
            $request->user(),
            $invoiceStatus->id
        );

        return Inertia::render('InvoiceStatuses/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(InvoiceStatus $invoiceStatus, Request $request): Response
    {
        $this->authorize('update', $invoiceStatus);

        $data = $this->query->getById($request->user(), $invoiceStatus->id);

        return Inertia::render('InvoiceStatuses/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateInvoiceStatusRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateInvoiceStatusRequest $request,
        InvoiceStatus $invoiceStatus
    ): JsonResponse|RedirectResponse {
        $invoiceStatus = $this->management->update(
            $request,
            $invoiceStatus
        );

        if ($request->wantsJson()) {
            return response()->json($invoiceStatus);
        }

        return redirect()->route('invoice-statuses.index', $invoiceStatus->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * invoiceStatus instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        InvoiceStatus $invoiceStatus
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $invoiceStatus);

        $this->management->destroy(
            $invoiceStatus,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('invoice-statuses.index');
    }

    /**
     * Restore a soft-deleted invoiceStatus.
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
        $invoiceStatus = InvoiceStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $invoiceStatus);

        $this->management->restore(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('invoice-statuses.index');
    }

    /**
     * Permanently delete a soft-deleted invoiceStatus.
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
        $invoiceStatus = InvoiceStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $invoiceStatus);

        $this->management->forceDelete(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('invoice-statuses.index');
    }

    /**
     * Bulk soft-delete multiple invoice statuses.
     *
     * Authorises each invoice status individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:invoice_statuses,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (InvoiceStatus $invoiceStatus) => $this->authorize('delete', $invoiceStatus)
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('invoice-statuses.index');
    }

    /**
     * Bulk restore multiple soft-deleted invoice statuses.
     *
     * Authorises each invoice status individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:invoice_statuses,id'],
        ]);

        $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (InvoiceStatus $invoiceStatus) => $this->authorize('restore', $invoiceStatus)
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('invoice-statuses.index');
    }
}

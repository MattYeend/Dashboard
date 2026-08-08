<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketStatuses\ImportTicketStatusRequest;
use App\Http\Requests\TicketStatuses\StoreTicketStatusRequest;
use App\Http\Requests\TicketStatuses\UpdateTicketStatusRequest;
use App\Models\TicketStatus;
use App\Services\TicketStatuses\ManagementService;
use App\Services\TicketStatuses\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketStatusController extends Controller
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
     * Passes paginated ticket statuses to the TicketStatuses/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', TicketStatus::class);

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

        return Inertia::render('TicketStatuses/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', TicketStatus::class);

        return Inertia::render('TicketStatuses/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreTicketStatusRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(
        StoreTicketStatusRequest $request
    ): JsonResponse|RedirectResponse {
        $ticketStatus = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($ticketStatus, 201);
        }

        return redirect()->route('ticket-statuses.show', $ticketStatus->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single ticketStatus to the TicketStatuses/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        TicketStatus $ticketStatus,
        Request $request
    ): Response {
        $this->authorize('view', $ticketStatus);

        $data = $this->query->getById(
            $request->user(),
            $ticketStatus->id
        );

        return Inertia::render('TicketStatuses/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(TicketStatus $ticketStatus, Request $request): Response
    {
        $this->authorize('update', $ticketStatus);

        $data = $this->query->getById($request->user(), $ticketStatus->id);

        return Inertia::render('TicketStatuses/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateTicketStatusRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateTicketStatusRequest $request,
        TicketStatus $ticketStatus
    ): JsonResponse|RedirectResponse {
        $ticketStatus = $this->management->update(
            $request,
            $ticketStatus
        );

        if ($request->wantsJson()) {
            return response()->json($ticketStatus);
        }

        return redirect()->route('ticket-statuses.show', $ticketStatus->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * ticketStatus instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        TicketStatus $ticketStatus
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $ticketStatus);

        $this->management->destroy(
            $ticketStatus,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('ticket-statuses.index');
    }

    /**
     * Restore a soft-deleted ticketStatus.
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
        $ticketStatus = TicketStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $ticketStatus);

        $this->management->restore(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('ticket-statuses.index');
    }

    /**
     * Permanently delete a soft-deleted ticketStatus.
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
        $ticketStatus = TicketStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $ticketStatus);

        $this->management->forceDelete(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('ticket-statuses.index');
    }

    /**
     * Bulk soft-delete multiple ticket statuses.
     *
     * Authorises each ticket status individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $result = $this->management->bulkDelete(
            $ids,
            $actor,
            fn (TicketStatus $ticketStatus) => $this->authorize('delete', $ticketStatus)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('ticket-statuses.index');
    }

    /**
     * Bulk restore multiple soft-deleted ticket statuses.
     *
     * Authorises each ticket status individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer'],
        ]);

        $result = $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (TicketStatus $ticketStatus) => $this->authorize('restore', $ticketStatus)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('ticket-statuses.index');
    }

    /**
     * Import ticket statuses from an uploaded CSV file.
     *
     * Authorisation is handled by ImportTicketStatusRequest::authorize().
     */
    public function import(ImportTicketStatusRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('ticket-statuses.index')
            ->with('import_result', $result);
    }

    /**
     * Export ticket statuses matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', TicketStatus::class);

        return $this->management->export(
            $request->only(['search', 'trashed'])
        );
    }
}

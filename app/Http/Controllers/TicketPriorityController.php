<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketPriorities\ImportTicketPriorityRequest;
use App\Http\Requests\TicketPriorities\StoreTicketPriorityRequest;
use App\Http\Requests\TicketPriorities\UpdateTicketPriorityRequest;
use App\Models\TicketPriority;
use App\Services\TicketPriorities\ManagementService;
use App\Services\TicketPriorities\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketPriorityController extends Controller
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
     * Passes paginated ticket priorities to the PipelineStatuses/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', TicketPriority::class);

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

        return Inertia::render('PipelineStatuses/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', TicketPriority::class);

        return Inertia::render('PipelineStatuses/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreTicketPriorityRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(
        StoreTicketPriorityRequest $request
    ): JsonResponse|RedirectResponse {
        $ticketPriority = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($ticketPriority, 201);
        }

        return redirect()->route('ticket-priorities.show', $ticketPriority->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single ticketPriority to the PipelineStatuses/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        TicketPriority $ticketPriority,
        Request $request
    ): Response {
        $this->authorize('view', $ticketPriority);

        $data = $this->query->getById(
            $request->user(),
            $ticketPriority->id
        );

        return Inertia::render('PipelineStatuses/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(TicketPriority $ticketPriority, Request $request): Response
    {
        $this->authorize('update', $ticketPriority);

        $data = $this->query->getById($request->user(), $ticketPriority->id);

        return Inertia::render('PipelineStatuses/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateTicketPriorityRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateTicketPriorityRequest $request,
        TicketPriority $ticketPriority
    ): JsonResponse|RedirectResponse {
        $ticketPriority = $this->management->update(
            $request,
            $ticketPriority
        );

        if ($request->wantsJson()) {
            return response()->json($ticketPriority);
        }

        return redirect()->route('ticket-priorities.show', $ticketPriority->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * ticketPriority instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        TicketPriority $ticketPriority
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $ticketPriority);

        $this->management->destroy(
            $ticketPriority,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('ticket-priorities.index');
    }

    /**
     * Restore a soft-deleted ticketPriority.
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
        $ticketPriority = TicketPriority::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $ticketPriority);

        $this->management->restore(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('ticket-priorities.index');
    }

    /**
     * Permanently delete a soft-deleted ticketPriority.
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
        $ticketPriority = TicketPriority::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $ticketPriority);

        $this->management->forceDelete(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('ticket-priorities.index');
    }

    /**
     * Bulk soft-delete multiple ticket priorities.
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
            fn (TicketPriority $ticketPriority) => $this->authorize('delete', $ticketPriority)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('ticket-priorities.index');
    }

    /**
     * Bulk restore multiple soft-deleted ticket priorities.
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
            fn (TicketPriority $ticketPriority) => $this->authorize('restore', $ticketPriority)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('ticket-priorities.index');
    }

    /**
     * Import ticket priorities from an uploaded CSV file.
     *
     * Authorisation is handled by ImportTicketPriorityRequest::authorize().
     */
    public function import(ImportTicketPriorityRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('ticket-priorities.index')
            ->with('import_result', $result);
    }

    /**
     * Export ticket priorities matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', TicketPriority::class);

        return $this->management->export(
            $request->only(['search', 'trashed'])
        );
    }
}

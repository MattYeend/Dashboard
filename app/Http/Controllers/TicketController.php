<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tickets\ImportTicketRequest;
use App\Http\Requests\Tickets\StoreTicketRequest;
use App\Http\Requests\Tickets\UpdateTicketRequest;
use App\Models\Ticket;
use App\Services\Tickets\ManagementService;
use App\Services\Tickets\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
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
     * Passes paginated tickets to the Tickets/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Ticket::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
                'ticket_status_id',
                'ticket_priority_id',
                'assigned_to',
                'due_date_from',
                'due_date_to',
                'label_id',
            ])
        );

        return Inertia::render('Tickets/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Ticket::class);

        return Inertia::render('Tickets/Create', $this->query->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreTicketRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(
        StoreTicketRequest $request
    ): JsonResponse|RedirectResponse {
        $ticket = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($ticket, 201);
        }

        return redirect()->route('tickets.show', $ticket->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single ticket to the Tickets/Show Inertia page.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        Ticket $ticket,
        Request $request
    ): Response {
        $this->authorize('view', $ticket);

        $data = $this->query->getById(
            $request->user(),
            $ticket->id
        );

        return Inertia::render('Tickets/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(Ticket $ticket, Request $request): Response
    {
        $this->authorize('update', $ticket);

        $data = array_merge(
            $this->query->getById($request->user(), $ticket->id),
            $this->query->getFormData()
        );

        return Inertia::render('Tickets/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateTicketRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateTicketRequest $request,
        Ticket $ticket
    ): JsonResponse|RedirectResponse {
        $ticket = $this->management->update(
            $request,
            $ticket
        );

        if ($request->wantsJson()) {
            return response()->json($ticket);
        }

        return redirect()->route('tickets.show', $ticket->id);
    }

    /**
     * Mark the specified ticket as resolved.
     *
     * Authorises via the 'update' policy before proceeding.
     */
    public function resolve(
        Request $request,
        Ticket $ticket
    ): JsonResponse|RedirectResponse {
        $this->authorize('update', $ticket);

        $ticket = $this->management->resolve($ticket, $request->user());

        if ($request->wantsJson()) {
            return response()->json($ticket);
        }

        return redirect()->route('tickets.show', $ticket->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * ticket instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Ticket $ticket
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $ticket);

        $this->management->destroy(
            $ticket,
            $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('tickets.index');
    }

    /**
     * Restore a soft-deleted ticket.
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
        $ticket = Ticket::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $ticket);

        $this->management->restore(
            $id,
            $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('tickets.index');
    }

    /**
     * Permanently delete a soft-deleted ticket.
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
        $ticket = Ticket::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $ticket);

        $this->management->forceDelete(
            $id,
            $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('tickets.index');
    }

    /**
     * Bulk soft-delete multiple tickets.
     *
     * Authorises each ticket individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer'],
        ]);

        $result = $this->management->bulkDelete(
            $validated['ids'],
            $request->user(),
            fn (Ticket $ticket) => $this->authorize('delete', $ticket)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('tickets.index');
    }

    /**
     * Bulk restore multiple soft-deleted tickets.
     *
     * Authorises each ticket individually via the 'restore' policy.
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
            fn (Ticket $ticket) => $this->authorize('restore', $ticket)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('tickets.index');
    }

    /**
     * Import tickets from an uploaded CSV file.
     *
     * Authorisation is handled by ImportTicketRequest::authorize().
     */
    public function import(ImportTicketRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('tickets.index')
            ->with('import_result', $result);
    }

    /**
     * Export tickets matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', Ticket::class);

        return $this->management->export(
            $request->only(['search', 'trashed'])
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationBroadcasts\StoreNotificationBroadcastRequest;
use App\Http\Requests\NotificationBroadcasts\UpdateNotificationBroadcastRequest;
use App\Models\NotificationBroadcast;
use App\Services\NotificationBroadcasts\ManagementService;
use App\Services\NotificationBroadcasts\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationBroadcastController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        protected readonly ManagementService $management,
        protected readonly QueryService $query,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * Passes paginated notification broadcasts to the
     * NotificationBroadcasts/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', NotificationBroadcast::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'audience_type',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render('NotificationBroadcasts/Index', $data);
    }

    /**
     * Show the form for creating a new notification broadcast.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', NotificationBroadcast::class);

        return Inertia::render('NotificationBroadcasts/Create', $this->query->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreNotificationBroadcastRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(StoreNotificationBroadcastRequest $request): JsonResponse|RedirectResponse
    {
        $notificationBroadcast = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($notificationBroadcast, 201);
        }

        return redirect()->route('notification-broadcasts.show', $notificationBroadcast->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single notification broadcast to the
     * NotificationBroadcasts/Show Inertia page.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        NotificationBroadcast $notificationBroadcast,
        Request $request
    ): Response {
        $this->authorize('view', $notificationBroadcast);

        $data = $this->query->getById(
            $request->user(),
            $notificationBroadcast->id
        );

        return Inertia::render('NotificationBroadcasts/Show', $data);
    }

    /**
     * Show the form for editing an existing notification broadcast.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        NotificationBroadcast $notificationBroadcast,
        Request $request
    ): Response {
        $this->authorize('update', $notificationBroadcast);

        $data = array_merge(
            $this->query->getById($request->user(), $notificationBroadcast->id),
            $this->query->getFormData(),
        );

        return Inertia::render('NotificationBroadcasts/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateNotificationBroadcastRequest,
     * which also implicitly authorises the operation via its authorize()
     * method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     */
    public function update(
        UpdateNotificationBroadcastRequest $request,
        NotificationBroadcast $notificationBroadcast
    ): JsonResponse|RedirectResponse {
        $notificationBroadcast = $this->management->update($request, $notificationBroadcast);

        if ($request->wantsJson()) {
            return response()->json($notificationBroadcast);
        }

        return redirect()->route('notification-broadcasts.show', $notificationBroadcast->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * notification broadcast instance is still fully accessible during
     * logging.
     */
    public function destroy(
        Request $request,
        NotificationBroadcast $notificationBroadcast
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $notificationBroadcast);

        $this->management->destroy($notificationBroadcast, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('notification-broadcasts.index');
    }

    /**
     * Restore a soft-deleted notification broadcast.
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
        $notificationBroadcast = NotificationBroadcast::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $notificationBroadcast);

        $this->management->restore($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('notification-broadcasts.index');
    }

    /**
     * Permanently delete a soft-deleted notification broadcast.
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
        $notificationBroadcast = NotificationBroadcast::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $notificationBroadcast);

        $this->management->forceDelete($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('notification-broadcasts.index');
    }

    /**
     * Bulk soft-delete multiple notification broadcasts.
     *
     * Authorises each notification broadcast individually via the 'delete'
     * policy.
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
            fn (NotificationBroadcast $notificationBroadcast) => $this->authorize('delete', $notificationBroadcast)
        );

        if (request()->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('notification-broadcasts.index');
    }

    /**
     * Bulk restore multiple soft-deleted notification broadcasts.
     *
     * Authorises each notification broadcast individually via the
     * 'restore' policy.
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
            fn (NotificationBroadcast $notificationBroadcast) => $this->authorize('restore', $notificationBroadcast)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('notification-broadcasts.index');
    }

    /**
     * Send a notification broadcast to its configured audience.
     *
     * Authorises via the 'send' policy before proceeding. The policy
     * itself also blocks a broadcast being sent twice.
     */
    public function send(
        Request $request,
        NotificationBroadcast $notificationBroadcast
    ): JsonResponse|RedirectResponse {
        $this->authorize('send', $notificationBroadcast);

        $notificationBroadcast = $this->management->send($notificationBroadcast, $request->user());

        if ($request->wantsJson()) {
            return response()->json($notificationBroadcast);
        }

        return redirect()->route('notification-broadcasts.show', $notificationBroadcast->id);
    }
}

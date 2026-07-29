<?php

namespace App\Http\Controllers;

use App\Http\Requests\DealStatuses\StoreDealStatusRequest;
use App\Http\Requests\DealStatuses\UpdateDealStatusRequest;
use App\Models\DealStatus;
use App\Services\DealStatuses\ManagementService;
use App\Services\DealStatuses\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DealStatusController extends Controller
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
     * Passes paginated deal statuses to the DealStatuses/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', DealStatus::class);

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

        return Inertia::render('DealStatuses/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', DealStatus::class);

        return Inertia::render('DealStatuses/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreDealStatusRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(
        StoreDealStatusRequest $request
    ): JsonResponse|RedirectResponse {
        $dealStatus = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($dealStatus, 201);
        }

        return redirect()->route('deal-statuses.show', $dealStatus->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single dealStatus to the DealStatuses/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        DealStatus $dealStatus,
        Request $request
    ): Response {
        $this->authorize('view', $dealStatus);

        $data = $this->query->getById(
            $request->user(),
            $dealStatus->id
        );

        return Inertia::render('DealStatuses/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(DealStatus $dealStatus, Request $request): Response
    {
        $this->authorize('update', $dealStatus);

        $data = $this->query->getById($request->user(), $dealStatus->id);

        return Inertia::render('DealStatuses/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateDealStatusRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateDealStatusRequest $request,
        DealStatus $dealStatus
    ): JsonResponse|RedirectResponse {
        $dealStatus = $this->management->update(
            $request,
            $dealStatus
        );

        if ($request->wantsJson()) {
            return response()->json($dealStatus);
        }

        return redirect()->route('deal-statuses.show', $dealStatus->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * deal status instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        DealStatus $dealStatus
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $dealStatus);

        $this->management->destroy(
            $dealStatus,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('deal-statuses.index');
    }

    /**
     * Restore a soft-deleted deal status.
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
        $dealStatus = DealStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $dealStatus);

        $this->management->restore(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('deal-statuses.index');
    }

    /**
     * Permanently delete a soft-deleted deal status.
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
        $dealStatus = DealStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $dealStatus);

        $this->management->forceDelete(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('deal-statuses.index');
    }

    /**
     * Bulk soft-delete multiple deal statuses.
     *
     * Authorises each deal status individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:deal_statuses,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (DealStatus $dealStatus) => $this->authorize('delete', $dealStatus)
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('deal-statuses.index');
    }

    /**
     * Bulk restore multiple soft-deleted deal statuses.
     *
     * Authorises each deal status individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:deal_statuses,id'],
        ]);

        $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (DealStatus $dealStatus) => $this->authorize('restore', $dealStatus)
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('deal-statuses.index');
    }
}

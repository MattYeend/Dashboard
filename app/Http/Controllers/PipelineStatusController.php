<?php

namespace App\Http\Controllers;

use App\Http\Requests\PipelineStatuses\ImportPipelineStatusRequest;
use App\Http\Requests\PipelineStatuses\StorePipelineStatusRequest;
use App\Http\Requests\PipelineStatuses\UpdatePipelineStatusRequest;
use App\Models\PipelineStatus;
use App\Services\PipelineStatuses\ManagementService;
use App\Services\PipelineStatuses\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PipelineStatusController extends Controller
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
     * Passes paginated pipeline statuses to the PipelineStatuses/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PipelineStatus::class);

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
        $this->authorize('create', PipelineStatus::class);

        return Inertia::render('PipelineStatuses/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StorePipelineStatusRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(
        StorePipelineStatusRequest $request
    ): JsonResponse|RedirectResponse {
        $pipelineStatus = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($pipelineStatus, 201);
        }

        return redirect()->route('pipeline-statuses.show', $pipelineStatus->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single pipelineStatus to the PipelineStatuses/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        PipelineStatus $pipelineStatus,
        Request $request
    ): Response {
        $this->authorize('view', $pipelineStatus);

        $data = $this->query->getById(
            $request->user(),
            $pipelineStatus->id
        );

        return Inertia::render('PipelineStatuses/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(PipelineStatus $pipelineStatus, Request $request): Response
    {
        $this->authorize('update', $pipelineStatus);

        $data = $this->query->getById($request->user(), $pipelineStatus->id);

        return Inertia::render('PipelineStatuses/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdatePipelineStatusRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdatePipelineStatusRequest $request,
        PipelineStatus $pipelineStatus
    ): JsonResponse|RedirectResponse {
        $pipelineStatus = $this->management->update(
            $request,
            $pipelineStatus
        );

        if ($request->wantsJson()) {
            return response()->json($pipelineStatus);
        }

        return redirect()->route('pipeline-statuses.show', $pipelineStatus->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * pipelineStatus instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        PipelineStatus $pipelineStatus
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $pipelineStatus);

        $this->management->destroy(
            $pipelineStatus,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('pipeline-statuses.index');
    }

    /**
     * Restore a soft-deleted pipelineStatus.
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
        $pipelineStatus = PipelineStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $pipelineStatus);

        $this->management->restore(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('pipeline-statuses.index');
    }

    /**
     * Permanently delete a soft-deleted pipelineStatus.
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
        $pipelineStatus = PipelineStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $pipelineStatus);

        $this->management->forceDelete(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('pipeline-statuses.index');
    }

    /**
     * Bulk soft-delete multiple pipeline statuses.
     *
     * Authorises each pipeline status individually via the 'delete' policy.
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
            fn (PipelineStatus $pipelineStatus) => $this->authorize('delete', $pipelineStatus)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('pipeline-statuses.index');
    }

    /**
     * Bulk restore multiple soft-deleted pipeline statuses.
     *
     * Authorises each pipeline status individually via the 'restore' policy.
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
            fn (PipelineStatus $pipelineStatus) => $this->authorize('restore', $pipelineStatus)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('pipeline-statuses.index');
    }

    /**
     * Import pipeline statuses from an uploaded CSV file.
     *
     * Authorisation is handled by ImportPipelineStatusRequest::authorize().
     */
    public function import(ImportPipelineStatusRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('pipeline-statuses.index')
            ->with('import_result', $result);
    }

    /**
     * Export pipeline statuses matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', PipelineStatus::class);

        return $this->management->export(
            $request->only(['search', 'trashed'])
        );
    }
}

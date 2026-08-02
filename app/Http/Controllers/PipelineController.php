<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pipelines\ImportPipelineRequest;
use App\Http\Requests\Pipelines\StorePipelineRequest;
use App\Http\Requests\Pipelines\UpdatePipelineRequest;
use App\Models\Pipeline;
use App\Services\Pipelines\ManagementService;
use App\Services\Pipelines\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PipelineController extends Controller
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
     * Passes paginated pipeline statuses to the Pipelines/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Pipeline::class);

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

        return Inertia::render('Pipelines/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Pipeline::class);

        return Inertia::render('Pipelines/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StorePipelineRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(
        StorePipelineRequest $request
    ): JsonResponse|RedirectResponse {
        $pipeline = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($pipeline, 201);
        }

        return redirect()->route('pipelines.show', $pipeline->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single pipeline to the Pipelines/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        Pipeline $pipeline,
        Request $request
    ): Response {
        $this->authorize('view', $pipeline);

        $data = $this->query->getById(
            $request->user(),
            $pipeline->id
        );

        return Inertia::render('Pipelines/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(Pipeline $pipeline, Request $request): Response
    {
        $this->authorize('update', $pipeline);

        $data = $this->query->getById($request->user(), $pipeline->id);

        return Inertia::render('Pipelines/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdatePipelineRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdatePipelineRequest $request,
        Pipeline $pipeline
    ): JsonResponse|RedirectResponse {
        $pipeline = $this->management->update(
            $request,
            $pipeline
        );

        if ($request->wantsJson()) {
            return response()->json($pipeline);
        }

        return redirect()->route('pipelines.show', $pipeline->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * pipeline instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Pipeline $pipeline
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $pipeline);

        $this->management->destroy(
            $pipeline,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('pipeline.index');
    }

    /**
     * Restore a soft-deleted pipeline.
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
        $pipeline = Pipeline::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $pipeline);

        $this->management->restore(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('pipeline.index');
    }

    /**
     * Permanently delete a soft-deleted pipeline.
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
        $pipeline = Pipeline::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $pipeline);

        $this->management->forceDelete(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('pipelines.index');
    }

    /**
     * Bulk soft-delete multiple pipeline.
     *
     * Authorises each pipeline individually via the 'delete' policy.
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
            fn (Pipeline $pipeline) => $this->authorize('delete', $pipeline)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('pipelines.index');
    }

    /**
     * Bulk restore multiple soft-deleted pipeline.
     *
     * Authorises each pipeline individually via the 'restore' policy.
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
            fn (Pipeline $pipeline) => $this->authorize('restore', $pipeline)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('pipelines.index');
    }

    /**
     * Import pipelines from an uploaded CSV file.
     *
     * Authorisation is handled by ImportPipelineRequest::authorize().
     */
    public function import(ImportPipelineRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('pipelines.index')
            ->with('import_result', $result);
    }

    /**
     * Export pipelines matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', Pipeline::class);

        return $this->management->export(
            $request->only(['search', 'trashed'])
        );
    }
}

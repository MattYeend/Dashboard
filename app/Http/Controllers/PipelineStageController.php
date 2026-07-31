<?php

namespace App\Http\Controllers;

use App\Http\Requests\PipelineStages\StorePipelineStageRequest;
use App\Http\Requests\PipelineStages\UpdatePipelineStageRequest;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Services\PipelineStages\ManagementService;
use App\Services\PipelineStages\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PipelineStageController extends Controller
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
     * Passes paginated pipeline stages, scoped to the given pipeline, to the
     * PipelineStages/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request, Pipeline $pipeline): Response
    {
        $this->authorize('viewAny', PipelineStage::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $pipeline,
            $request->only([
                'search',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render('PipelineStages/Index', [
            ...$data,
            'pipeline' => [
                'id' => $pipeline->id,
                'title' => $pipeline->title,
            ],
        ]);
    }

    /**
     * Show the form for creating a new pipeline stage.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(Pipeline $pipeline): Response
    {
        $this->authorize('create', PipelineStage::class);

        return Inertia::render('PipelineStages/Create', [
            'pipeline' => [
                'id' => $pipeline->id,
                'title' => $pipeline->title,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StorePipelineStageRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(StorePipelineStageRequest $request, Pipeline $pipeline): JsonResponse|RedirectResponse
    {
        $pipelineStage = $this->management->store($request, $pipeline);

        if ($request->wantsJson()) {
            return response()->json($pipelineStage, 201);
        }

        return redirect()->route('pipelines.stages.show', [$pipeline, $pipelineStage]);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single pipeline stage to the PipelineStages/Show Inertia page.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        Request $request,
        Pipeline $pipeline,
        PipelineStage $stage
    ): Response {
        $this->authorize('view', $stage);

        $data = $this->query->getById(
            $request->user(),
            $pipeline,
            $stage->id
        );

        return Inertia::render('PipelineStages/Show', $data);
    }

    /**
     * Show the form for editing an existing pipeline stage.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Request $request,
        Pipeline $pipeline,
        PipelineStage $stage
    ): Response {
        $this->authorize('update', $stage);

        $data = $this->query->getById(
            $request->user(),
            $pipeline,
            $stage->id
        );

        return Inertia::render('PipelineStages/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdatePipelineStageRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdatePipelineStageRequest $request,
        Pipeline $pipeline,
        PipelineStage $stage
    ): JsonResponse|RedirectResponse {
        $pipelineStage = $this->management->update($request, $stage);

        if ($request->wantsJson()) {
            return response()->json($pipelineStage);
        }

        return redirect()->route('pipelines.stages.show', [$pipeline, $pipelineStage]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * pipeline stage instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Pipeline $pipeline,
        PipelineStage $stage
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $stage);

        $this->management->destroy($stage, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('pipelines.stages.index', $pipeline);
    }

    /**
     * Restore a soft-deleted pipeline stage.
     *
     * Resolves the trashed model manually, scoped to the given pipeline,
     * since route model binding excludes soft-deleted records by default.
     *
     * Authorises via the 'restore' policy before proceeding.
     */
    public function restore(
        Request $request,
        Pipeline $pipeline,
        int $id
    ): JsonResponse|RedirectResponse {
        $stage = PipelineStage::onlyTrashed()
            ->where('pipeline_id', $pipeline->id)
            ->findOrFail($id);

        $this->authorize('restore', $stage);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('pipelines.stages.index', $pipeline);
    }

    /**
     * Permanently delete a soft-deleted pipeline stage.
     *
     * Resolves the trashed model manually, scoped to the given pipeline,
     * since route model binding excludes soft-deleted records by default.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(
        Request $request,
        Pipeline $pipeline,
        int $id
    ): JsonResponse|RedirectResponse {
        $stage = PipelineStage::onlyTrashed()
            ->where('pipeline_id', $pipeline->id)
            ->findOrFail($id);

        $this->authorize('forceDelete', $stage);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('pipelines.stages.index', $pipeline);
    }

    /**
     * Bulk soft-delete multiple pipeline stages within a pipeline.
     *
     * Authorises each pipeline stage individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request, Pipeline $pipeline): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($pipeline) {
                    $stage = PipelineStage::withTrashed()->find($value);

                    if ($stage && $stage->pipeline_id !== $pipeline->id) {
                        $fail('The selected '.$attribute.' is invalid.');
                    }
                },
            ],
        ]);

        $result = $this->management->bulkDelete(
            $pipeline,
            $validated['ids'],
            $request->user(),
            fn (PipelineStage $stage) => $this->authorize('delete', $stage)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('pipelines.stages.index', $pipeline);
    }

    /**
     * Bulk restore multiple soft-deleted pipeline stages within a pipeline.
     *
     * Authorises each pipeline stage individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request, Pipeline $pipeline): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($pipeline) {
                    $stage = PipelineStage::withTrashed()->find($value);

                    if ($stage && $stage->pipeline_id !== $pipeline->id) {
                        $fail('The selected '.$attribute.' is invalid.');
                    }
                },
            ],
        ]);

        $result = $this->management->bulkRestore(
            $pipeline,
            $validated['ids'],
            $request->user(),
            fn (PipelineStage $stage) => $this->authorize('restore', $stage)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('pipelines.stages.index', $pipeline);
    }
}

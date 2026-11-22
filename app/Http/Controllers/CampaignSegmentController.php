<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkIdsRequest;
use App\Http\Requests\CampaignSegments\ImportCampaignSegmentRequest;
use App\Http\Requests\CampaignSegments\StoreCampaignSegmentRequest;
use App\Http\Requests\CampaignSegments\UpdateCampaignSegmentRequest;
use App\Models\CampaignSegment;
use App\Services\CampaignSegments\ManagementService;
use App\Services\CampaignSegments\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CampaignSegmentController extends Controller
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
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CampaignSegment::class);

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

        return Inertia::render('CampaignSegments/Index', $data);
    }

    /**
     * Show the form for creating a new campaign segment.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', CampaignSegment::class);

        return Inertia::render(
            'CampaignSegments/Create',
            $this->query->getFormData()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreCampaignSegmentRequest.
     */
    public function store(StoreCampaignSegmentRequest $request): JsonResponse|RedirectResponse
    {
        $campaignSegment = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($campaignSegment, 201);
        }

        return redirect()
            ->route('campaign-segments.show', $campaignSegment->id)
            ->with('success', 'Campaign segment created.');
    }

    /**
     * Display the specified resource.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        CampaignSegment $campaignSegment,
        Request $request
    ): Response {
        $this->authorize('view', $campaignSegment);

        $data = array_merge(
            $this->query->getById(
                $request->user(),
                $campaignSegment
            ),
            ['canEdit' => $request->user()->can('update', $campaignSegment)],
        );

        return Inertia::render('CampaignSegments/Show', $data);
    }

    /**
     * Show the form for editing an existing campaign segment.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        CampaignSegment $campaignSegment,
        Request $request
    ): Response {
        $this->authorize('update', $campaignSegment);

        $data = array_merge(
            $this->query->getById(
                $request->user(),
                $campaignSegment
            ),
            $this->query->getFormData(),
        );

        return Inertia::render('CampaignSegments/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation and authorisation are handled upstream by UpdateCampaignSegmentRequest.
     */
    public function update(
        UpdateCampaignSegmentRequest $request,
        CampaignSegment $campaignSegment
    ): JsonResponse|RedirectResponse {
        $campaignSegment = $this->management->update($request, $campaignSegment);

        if ($request->wantsJson()) {
            return response()->json($campaignSegment);
        }

        return redirect()
            ->route('campaign-segments.show', $campaignSegment->id)
            ->with('success', 'Campaign segment updated.');
    }

    /**
     * Soft delete a campaign segment.
     *
     * Authorises via the 'delete' policy before proceeding. A segment used
     * by a scheduled or sending campaign is refused with a 403.
     */
    public function destroy(
        Request $request,
        CampaignSegment $campaignSegment
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $campaignSegment);

        $this->management->destroy($campaignSegment, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('campaign-segments.index')
            ->with('success', 'Campaign segment deleted.');
    }

    /**
     * Restore a soft-deleted campaign segment.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     */
    public function restore(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $campaignSegment = CampaignSegment::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $campaignSegment);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('campaign-segments.index')
            ->with('success', 'Campaign segment restored.');
    }

    /**
     * Permanently delete a soft-deleted campaign segment.
     *
     * Only records that are already soft-deleted can be force deleted, and
     * only when no campaign still references them.
     */
    public function forceDelete(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $campaignSegment = CampaignSegment::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $campaignSegment);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('campaign-segments.index')
            ->with('success', 'Campaign segment permanently deleted.');
    }

    /**
     * Bulk soft-delete multiple campaign segments.
     *
     * Authorises each segment individually via the 'delete' policy.
     */
    public function bulkDelete(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', CampaignSegment::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $request->user(),
            fn (CampaignSegment $campaignSegment) => $this->authorize('delete', $campaignSegment)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('campaign-segments.index')
            ->with('success', count($result['deleted']).' campaign segments deleted.');
    }

    /**
     * Bulk restore multiple soft-deleted campaign segments.
     *
     * Authorises each segment individually via the 'restore' policy.
     */
    public function bulkRestore(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', CampaignSegment::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $request->user(),
            fn (CampaignSegment $campaignSegment) => $this->authorize('restore', $campaignSegment)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('campaign-segments.index')
            ->with('success', count($result['restored']).' campaign segments restored.');
    }

    /**
     * Import campaign segments from an uploaded CSV file.
     *
     * Authorisation is handled by ImportCampaignSegmentRequest::authorize().
     */
    public function import(ImportCampaignSegmentRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('campaign-segments.index')
            ->with('import_result', $result);
    }

    /**
     * Export campaign segments matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', CampaignSegment::class);

        return $this->management->export(
            $request->only(['search', 'trashed'])
        );
    }

    /**
     * Parse and validate an uploaded CSV, returning a per-row preview
     * without persisting anything.
     *
     * Authorisation is handled by ImportCampaignSegmentRequest::authorize().
     */
    public function importPreview(ImportCampaignSegmentRequest $request): JsonResponse
    {
        $importer = $this->management->importer();
        $token = $importer->storeUpload($request->file('file'), $request->user()->id);

        try {
            $preview = $importer->preview($token, $request->user()->id);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['token' => $token, ...$preview]);
    }

    /**
     * Commit a previously previewed import, persisting valid rows only.
     *
     * Authorises via the 'import' policy before proceeding.
     */
    public function importCommit(Request $request): JsonResponse
    {
        $this->authorize('import', CampaignSegment::class);

        $request->validate(['token' => ['required', 'uuid']]);

        try {
            $result = $this->management->importer()->commit(
                $request->input('token'),
                $request->user()->id
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }
}
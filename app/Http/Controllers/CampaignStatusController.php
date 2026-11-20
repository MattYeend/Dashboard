<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkIdsRequest;
use App\Http\Requests\CampaignStatuses\ImportCampaignStatusRequest;
use App\Http\Requests\CampaignStatuses\StoreCampaignStatusRequest;
use App\Http\Requests\CampaignStatuses\UpdateCampaignStatusRequest;
use App\Models\CampaignStatus;
use App\Services\CampaignStatuses\ManagementService;
use App\Services\CampaignStatuses\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Passes paginated campaign statuses to the CampaignStatuses/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CampaignStatus::class);

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

        return Inertia::render('CampaignStatuses/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', CampaignStatus::class);

        return Inertia::render('CampaignStatuses/Create');
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
        StoreCampaignStatusRequest $request
    ): JsonResponse|RedirectResponse {
        $campaignStatus = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($campaignStatus, 201);
        }

        return redirect()->route('campaign-statuses.show', $campaignStatus->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single campaignStatus to the CampaignStatuses/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        CampaignStatus $campaignStatus,
        Request $request
    ): Response {
        $this->authorize('view', $campaignStatus);

        $data = $this->query->getById(
            $request->user(),
            $campaignStatus
        );

        return Inertia::render('CampaignStatuses/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(CampaignStatus $campaignStatus, Request $request): Response
    {
        $this->authorize('update', $campaignStatus);

        $data = $this->query->getById(
            $request->user(),
            $campaignStatus
        );

        return Inertia::render('CampaignStatuses/Edit', $data);
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
        UpdateCampaignStatusRequest $request,
        CampaignStatus $campaignStatus
    ): JsonResponse|RedirectResponse {
        $campaignStatus = $this->management->update(
            $request,
            $campaignStatus
        );

        if ($request->wantsJson()) {
            return response()->json($campaignStatus);
        }

        return redirect()->route('campaign-statuses.show', $campaignStatus->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * campaign status instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        CampaignStatus $campaignStatus
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $campaignStatus);

        $this->management->destroy(
            $campaignStatus,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('campaign-statuses.index');
    }

    /**
     * Restore a soft-deleted campaign status.
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
        $campaignStatus = CampaignStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $campaignStatus);

        $this->management->restore(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('campaign-statuses.index');
    }

    /**
     * Permanently delete a soft-deleted campaign status.
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
        $campaignStatus = CampaignStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $campaignStatus);

        $this->management->forceDelete(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('campaign-statuses.index');
    }

    /**
     * Bulk soft-delete multiple campaign statuses.
     *
     * Authorises each campaign status individually via the 'delete' policy.
     */
    public function bulkDelete(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', CampaignStatus::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $request->user(),
            fn (CampaignStatus $campaignStatus) => $this->authorize('delete', $campaignStatus)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('campaign-statuses.index');
    }

    /**
     * Bulk restore multiple soft-deleted campaign statuses.
     *
     * Authorises each campaign status individually via the 'restore' policy.
     */
    public function bulkRestore(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', CampaignStatus::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $request->user(),
            fn (CampaignStatus $campaignStatus) => $this->authorize('restore', $campaignStatus)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('campaign-statuses.index');
    }

    /**
     * Import campaign statuses from an uploaded CSV file.
     *
     * Authorisation is handled by ImportCampaignStatusRequest::authorize().
     */
    public function import(ImportCampaignStatusRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('campaign-statuses.index')
            ->with('import_result', $result);
    }

    /**
     * Export campaign statuses matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', CampaignStatus::class);

        return $this->management->export(
            $request->only(['search', 'trashed'])
        );
    }

    /**
     * Parse and validate an uploaded CSV, returning a per-row preview
     * without persisting anything.
     *
     * Authorisation is handled by ImportCampaignStatusRequest::authorize().
     */
    public function importPreview(ImportCampaignStatusRequest $request): JsonResponse
    {
        $importer = $this->management->importer();
        $token = $importer->storeUpload($request->file('file'), $request->user()->id);

        return response()->json(['token' => $token, ...$importer->preview($token, $request->user()->id)]);
    }

    /**
     * Commit a previously previewed import, persisting valid rows only.
     *
     * Authorises via the 'import' policy before proceeding.
     */
    public function importCommit(Request $request): JsonResponse
    {
        $this->authorize('import', CampaignStatus::class);

        $request->validate(['token' => ['required', 'uuid']]);

        return response()->json($this->management->importer()->commit($request->input('token'), $request->user()->id));
    }
}

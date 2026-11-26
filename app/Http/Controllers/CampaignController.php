<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Campaigns\CancelCampaign;
use App\Actions\Campaigns\PauseCampaign;
use App\Actions\Campaigns\ResumeCampaign;
use App\Actions\Campaigns\ScheduleCampaign;
use App\Exceptions\CampaignTransitionException;
use App\Http\Requests\BulkIdsRequest;
use App\Http\Requests\Campaigns\ImportCampaignRequest;
use App\Http\Requests\Campaigns\ScheduleCampaignRequest;
use App\Http\Requests\Campaigns\StoreCampaignRequest;
use App\Http\Requests\Campaigns\UpdateCampaignRequest;
use App\Models\Campaign;
use App\Services\Campaigns\ManagementService;
use App\Services\Campaigns\QueryService;
use Closure;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CampaignController extends Controller
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
        $this->authorize('viewAny', Campaign::class);

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

        return Inertia::render('Campaigns/Index', $data);
    }

    /**
     * Show the form for creating a new campaign.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Campaign::class);

        return Inertia::render(
            'Campaigns/Create',
            $this->query->getFormData()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation and authorisation are handled upstream by
     * StoreCampaignRequest.
     */
    public function store(StoreCampaignRequest $request): JsonResponse|RedirectResponse
    {
        $campaign = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($campaign, 201);
        }

        return redirect()
            ->route('campaigns.show', $campaign->id)
            ->with('success', 'Campaign created.');
    }

    /**
     * Display the specified resource.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        Campaign $campaign,
        Request $request
    ): Response {
        $this->authorize('view', $campaign);

        $data = array_merge(
            $this->query->getById(
                $request->user(),
                $campaign
            ),
            ['canEdit' => $request->user()->can('update', $campaign)],
        );

        return Inertia::render('Campaigns/Show', $data);
    }

    /**
     * Show the form for editing an existing campaign.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Campaign $campaign,
        Request $request
    ): Response {
        $this->authorize('update', $campaign);

        $data = array_merge(
            $this->query->getById(
                $request->user(),
                $campaign
            ),
            $this->query->getFormData(),
        );

        return Inertia::render('Campaigns/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation and authorisation are handled upstream by
     * UpdateCampaignRequest.
     */
    public function update(
        UpdateCampaignRequest $request,
        Campaign $campaign
    ): JsonResponse|RedirectResponse {
        $campaign = $this->management->update($request, $campaign);

        if ($request->wantsJson()) {
            return response()->json($campaign);
        }

        return redirect()
            ->route('campaigns.show', $campaign->id)
            ->with('success', 'Campaign updated.');
    }

    /**
     * Soft delete a campaign.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(
        Request $request,
        Campaign $campaign
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $campaign);

        $this->management->destroy($campaign, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('campaigns.index')
            ->with('success', 'Campaign deleted.');
    }

    /**
     * Restore a soft-deleted campaign.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     */
    public function restore(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $campaign = Campaign::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $campaign);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('campaigns.index')
            ->with('success', 'Campaign restored.');
    }

    /**
     * Permanently delete a soft-deleted campaign.
     *
     * Only records that are already soft-deleted can be force deleted.
     */
    public function forceDelete(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $campaign = Campaign::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $campaign);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('campaigns.index')
            ->with('success', 'Campaign permanently deleted.');
    }

    /**
     * Bulk soft-delete multiple campaigns.
     *
     * Authorises each campaign individually via the 'delete' policy.
     */
    public function bulkDelete(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', Campaign::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $request->user(),
            fn (Campaign $campaign) => $this->authorize('delete', $campaign)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('campaigns.index')
            ->with('success', count($result['deleted']).' campaigns deleted.');
    }

    /**
     * Bulk restore multiple soft-deleted campaigns.
     *
     * Authorises each campaign individually via the 'restore' policy.
     */
    public function bulkRestore(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', Campaign::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $request->user(),
            fn (Campaign $campaign) => $this->authorize('restore', $campaign)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('campaigns.index')
            ->with('success', count($result['restored']).' campaigns restored.');
    }

    /**
     * Import campaigns from an uploaded CSV file.
     *
     * Authorisation is handled by ImportCampaignRequest::authorize().
     */
    public function import(ImportCampaignRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('campaigns.index')
            ->with('import_result', $result);
    }

    /**
     * Export campaigns matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', Campaign::class);

        return $this->management->export(
            $request->only(['search', 'trashed'])
        );
    }

    /**
     * Parse and validate an uploaded CSV, returning a per-row preview
     * without persisting anything.
     *
     * Authorisation is handled by ImportCampaignRequest::authorize().
     */
    public function importPreview(ImportCampaignRequest $request): JsonResponse
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
        $this->authorize('import', Campaign::class);

        $request->validate(['token' => ['required', 'uuid']]);

        try {
            $result = $this->management->importer()->commit(
                $request->input('token'),
                $request->user()->id
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }

    /**
     * Schedule a draft campaign.
     *
     * Validation and authorisation are handled upstream by
     * ScheduleCampaignRequest.
     */
    public function schedule(
        ScheduleCampaignRequest $request,
        Campaign $campaign,
        ScheduleCampaign $action
    ): JsonResponse|RedirectResponse {
        return $this->transition(
            $request,
            fn () => $action->execute($campaign, Date::parse($request->validated('scheduled_at'))),
            'Campaign scheduled.'
        );
    }

    /**
     * Pause a sending campaign.
     *
     * Authorises via the 'pause' policy before proceeding.
     */
    public function pause(
        Request $request,
        Campaign $campaign,
        PauseCampaign $action
    ): JsonResponse|RedirectResponse {
        $this->authorize('pause', $campaign);

        return $this->transition($request, fn () => $action->execute($campaign), 'Campaign paused.');
    }

    /**
     * Resume a paused campaign.
     *
     * Authorises via the 'resume' policy before proceeding.
     */
    public function resume(
        Request $request,
        Campaign $campaign,
        ResumeCampaign $action
    ): JsonResponse|RedirectResponse {
        $this->authorize('resume', $campaign);

        return $this->transition($request, fn () => $action->execute($campaign), 'Campaign resumed.');
    }

    /**
     * Cancel a draft, scheduled or paused campaign.
     *
     * Authorises via the 'cancel' policy before proceeding.
     */
    public function cancel(
        Request $request,
        Campaign $campaign,
        CancelCampaign $action
    ): JsonResponse|RedirectResponse {
        $this->authorize('cancel', $campaign);

        return $this->transition($request, fn () => $action->execute($campaign), 'Campaign cancelled.');
    }

    /**
     * Run a status transition and turn a refusal into a validation style
     * error instead of a server error.
     *
     * @param  Closure(): Campaign  $callback
     */
    private function transition(
        Request $request,
        Closure $callback,
        string $message
    ): JsonResponse|RedirectResponse {
        try {
            $campaign = $callback();
        } catch (CampaignTransitionException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['campaign' => $e->getMessage()]);
        }

        if ($request->wantsJson()) {
            return response()->json($campaign);
        }

        return back()->with('success', $message);
    }
}

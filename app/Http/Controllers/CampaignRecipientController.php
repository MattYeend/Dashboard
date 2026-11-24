<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkIdsRequest;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Services\CampaignRecipients\ManagementService;
use App\Services\CampaignRecipients\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CampaignRecipientController extends Controller
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
     * Display a listing of the campaign's recipients.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Campaign $campaign, Request $request): Response
    {
        $this->authorize('viewAny', CampaignRecipient::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $campaign,
            $request->only([
                'search',
                'status',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render('CampaignRecipients/Index', $data);
    }

    /**
     * Display the specified recipient.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        Campaign $campaign,
        CampaignRecipient $campaignRecipient,
        Request $request
    ): Response {
        $this->authorize('view', $campaignRecipient);

        return Inertia::render(
            'CampaignRecipients/Show',
            $this->query->getById(
                $request->user(),
                $campaign,
                $campaignRecipient
            )
        );
    }

    /**
     * Soft delete a campaign recipient.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(
        Request $request,
        Campaign $campaign,
        CampaignRecipient $campaignRecipient
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $campaignRecipient);

        $this->management->destroy($campaignRecipient, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('campaigns.campaign-recipients.index', $campaign->id)
            ->with('success', 'Campaign recipient deleted.');
    }

    /**
     * Restore a soft-deleted campaign recipient.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     */
    public function restore(
        Campaign $campaign,
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $campaignRecipient = CampaignRecipient::onlyTrashed()
            ->where('campaign_id', $campaign->id)
            ->findOrFail($id);

        $this->authorize('restore', $campaignRecipient);

        $this->management->restore($id, $campaign, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('campaigns.campaign-recipients.index', $campaign->id)
            ->with('success', 'Campaign recipient restored.');
    }

    /**
     * Permanently delete a soft-deleted campaign recipient.
     *
     * Only records that are already soft-deleted can be force deleted.
     */
    public function forceDelete(
        Campaign $campaign,
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $campaignRecipient = CampaignRecipient::onlyTrashed()
            ->where('campaign_id', $campaign->id)
            ->findOrFail($id);

        $this->authorize('forceDelete', $campaignRecipient);

        $this->management->forceDelete($id, $campaign, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('campaigns.campaign-recipients.index', $campaign->id)
            ->with('success', 'Campaign recipient permanently deleted.');
    }

    /**
     * Bulk soft-delete multiple campaign recipients.
     *
     * Authorises each recipient individually via the 'delete' policy.
     */
    public function bulkDelete(
        Campaign $campaign,
        BulkIdsRequest $request
    ): JsonResponse|RedirectResponse {
        $this->authorize('bulkDelete', CampaignRecipient::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $campaign,
            $request->user(),
            fn (CampaignRecipient $campaignRecipient) => $this->authorize('delete', $campaignRecipient)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('campaigns.campaign-recipients.index', $campaign->id)
            ->with('success', count($result['deleted']).' campaign recipients deleted.');
    }

    /**
     * Bulk restore multiple soft-deleted campaign recipients.
     *
     * Authorises each recipient individually via the 'restore' policy.
     */
    public function bulkRestore(
        Campaign $campaign,
        BulkIdsRequest $request
    ): JsonResponse|RedirectResponse {
        $this->authorize('bulkRestore', CampaignRecipient::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $campaign,
            $request->user(),
            fn (CampaignRecipient $campaignRecipient) => $this->authorize('restore', $campaignRecipient)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('campaigns.campaign-recipients.index', $campaign->id)
            ->with('success', count($result['restored']).' campaign recipients restored.');
    }

    /**
     * Export the campaign's recipients matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(
        Campaign $campaign,
        Request $request
    ): JsonResponse|RedirectResponse|StreamedResponse {
        $this->authorize('export', CampaignRecipient::class);

        return $this->management->export(
            $campaign,
            $request->only(['search', 'status', 'trashed'])
        );
    }
}

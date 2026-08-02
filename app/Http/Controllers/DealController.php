<?php

namespace App\Http\Controllers;

use App\Http\Requests\Deals\ImportDealRequest;
use App\Http\Requests\Deals\StoreDealRequest;
use App\Http\Requests\Deals\UpdateDealRequest;
use App\Models\Deal;
use App\Services\Deals\ManagementService;
use App\Services\Deals\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DealController extends Controller
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
     * Passes paginated deals to the Deal/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Deal::class);

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

        return Inertia::render('Deals/Index', $data);
    }

    /**
     * Show the form for creating a new deal.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Deal::class);

        return Inertia::render('Deals/Create', $this->query->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreIndustryRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(StoreDealRequest $request): JsonResponse|RedirectResponse
    {
        $deal = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($deal, 201);
        }

        return redirect()->route('deals.show', $deal->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single deal to the Deal/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        Deal $deal,
        Request $request
    ): Response {
        $this->authorize('view', $deal);

        $data = $this->query->getById(
            $request->user(),
            $deal->id
        );

        return Inertia::render('Deals/Show', $data);
    }

    /**
     * Show the form for editing an existing deal.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Deal $deal,
        Request $request
    ): Response {
        $this->authorize('update', $deal);

        $data = array_merge(
            $this->query->getById($request->user(), $deal->id),
            $this->query->getFormData()
        );

        return Inertia::render('Deals/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateIndustryRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateDealRequest $request,
        Deal $deal
    ): JsonResponse|RedirectResponse {
        $deal = $this->management->update($request, $deal);

        if ($request->wantsJson()) {
            return response()->json($deal);
        }

        return redirect()->route('deals.show', $deal->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * deal instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Deal $deal
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $deal);

        $this->management->destroy($deal, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('deals.index');
    }

    /**
     * Restore a soft-deleted deal.
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
        $deal = Deal::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $deal);

        $this->management->restore($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('deals.index');
    }

    /**
     * Permanently delete a soft-deleted deal.
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
        $deal = Deal::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $deal);

        $this->management->forceDelete($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('deals.index');
    }

    /**
     * Bulk soft-delete multiple deals.
     *
     * Authorises each deal individually via the 'delete' policy.
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
            fn (Deal $deal) => $this->authorize('delete', $deal)
        );

        if (request()->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('deals.index');
    }

    /**
     * Bulk restore multiple soft-deleted deals.
     *
     * Authorises each deal individually via the 'restore' policy.
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
            fn (Deal $deal) => $this->authorize('restore', $deal)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('deals.index');
    }

    /**
     * Import deals from an uploaded CSV file.
     *
     * Authorisation is handled by ImportDealRequest::authorize().
     */
    public function import(ImportDealRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('deals.index')
            ->with('import_result', $result);
    }

    /**
     * Export deals matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', Deal::class);

        return $this->management->export(
            $request->only(['search', 'trashed'])
        );
    }
}

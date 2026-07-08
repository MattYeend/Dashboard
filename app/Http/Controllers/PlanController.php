<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Models\Plan;
use App\Services\Plans\ManagementService;
use App\Services\Plans\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
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
     * Passes paginated plans to the Plans/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Plan::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only(['search', 'is_active', 'trashed', 'sort_by', 'sort_direction', 'per_page'])

        );

        return Inertia::render('Plans/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Plan::class);

        return Inertia::render('Plans/Create', $this->query->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StorePlanRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(
        StorePlanRequest $request
    ): JsonResponse|RedirectResponse {
        $plan = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($plan, 201);
        }

        return redirect()->route('plans.show', $plan->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single plan to the Plans/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        Plan $plan,
        Request $request
    ): Response {
        $this->authorize('view', $plan);

        $data = $this->query->getById(
            $request->user(),
            $plan->id
        );

        return Inertia::render('Plans/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Plan $plan,
        Request $request
    ): Response {
        $this->authorize('update', $plan);

        $data = array_merge(
            $this->query->getById(
                $request->user(),
                $plan->id
            ),
            $this->query->getFormData()
        );

        return Inertia::render('Plans/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdatePlanRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdatePlanRequest $request,
        Plan $plan
    ): JsonResponse|RedirectResponse {
        $plan = $this->management->update(
            $request,
            $plan
        );

        if ($request->wantsJson()) {
            return response()->json($plan);
        }

        return redirect()->route('plans.show', $plan->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * plan instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Plan $plan
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $plan);

        $this->management->destroy(
            $plan,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('plans.index');
    }

    /**
     * Restore a soft-deleted plan.
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
        $plan = Plan::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $plan);

        $this->management->restore(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('plans.index');
    }

    /**
     * Permanently delete a soft-deleted plan.
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
        $plan = Plan::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $plan);

        $this->management->forceDelete(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('plans.index');
    }

    /**
     * Bulk soft-delete multiple plans.
     *
     * Authorises each plan individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:plans,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (Plan $plan) => $this->authorize('delete', $plan)
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('plans.index');
    }

    /**
     * Bulk restore multiple soft-deleted plans.
     *
     * Authorises each plan individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:plans,id'],
        ]);

        $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (Plan $plan) => $this->authorize('restore', $plan)
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('plans.index');
    }
}

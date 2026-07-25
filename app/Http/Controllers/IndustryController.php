<?php

namespace App\Http\Controllers;

use App\Http\Requests\Industries\StoreIndustryRequest;
use App\Http\Requests\Industries\UpdateIndustryRequest;
use App\Models\Industry;
use App\Services\Industries\ManagementService;
use App\Services\Industries\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndustryController extends Controller
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
     * Passes paginated industries to the Orders/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(
        Request $request
    ): Response {
        $this->authorize('viewAny', Industry::class);

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

        return Inertia::render('Industries/Index', $data);
    }

    /**
     * Show the form for creating a new industry.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Industry::class);

        return Inertia::render('Industries/Create', $this->query->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreIndustryRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(
        StoreIndustryRequest $request
    ): JsonResponse|RedirectResponse {
        $industry = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($industry, 201);
        }

        return redirect()->route('industries.show', $industry->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single industry to the Industries/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        Industry $industry,
        Request $request
    ): Response {
        $this->authorize('view', $industry);

        $data = $this->query->getById(
            $request->user(),
            $industry->id
        );

        return Inertia::render('Industries/Show', $data);
    }

    /**
     * Show the form for editing an existing industry.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Industry $industry,
        Request $request
    ): Response {
        $this->authorize('update', $industry);

        $data = $this->query->getById(
            $request->user(),
            $industry->id
        );

        return Inertia::render('Industries/Edit', $data);
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
        UpdateIndustryRequest $request,
        Industry $industry
    ): JsonResponse|RedirectResponse {
        $industry = $this->management->update(
            $request,
            $industry
        );

        if ($request->wantsJson()) {
            return response()->json($industry);
        }

        return redirect()->route('industries.show', $industry->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * industry instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Industry $industry
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $industry);

        $this->management->destroy(
            $industry,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('industries.index');
    }

    /**
     * Restore a soft-deleted industry.
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
        $industry = Industry::onlyTrashed()->findOrFail($id);

        $this->authorize(
            'restore',
            $industry
        );

        $this->management->restore(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('industries.index');
    }

    /**
     * Permanently delete a soft-deleted industry.
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
        $industry = Industry::onlyTrashed()->findOrFail($id);

        $this->authorize(
            'forceDelete',
            $industry
        );

        $this->management->forceDelete(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('industries.index');
    }

    /**
     * Bulk soft-delete multiple industries.
     *
     * Authorises each industry individually via the 'delete' policy.
     */
    public function bulkDelete(
        Request $request
    ): JsonResponse|RedirectResponse {
        $request->validate([
            'ids' => [
                'required',
                'array',
            ],
            'ids.*' => [
                'required',
                'integer',
                'exists:industries,id',
            ],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (Industry $industry) => $this->authorize('delete', $industry)
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('industries.index');
    }

    /**
     * Bulk restore multiple soft-deleted industries.
     *
     * Authorises each industry individually via the 'restore' policy.
     */
    public function bulkRestore(
        Request $request
    ): JsonResponse|RedirectResponse {
        $validated = $request->validate([
            'ids' => [
                'required',
                'array'],
            'ids.*' => [
                'required',
                'integer',
                'exists:industries,id',
            ],
        ]);

        $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (Industry $industry) => $this->authorize('restore', $industry)
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('industries.index');
    }
}

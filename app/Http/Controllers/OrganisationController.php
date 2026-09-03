<?php

namespace App\Http\Controllers;

use App\Http\Requests\Organisations\StoreOrganisationRequest;
use App\Http\Requests\Organisations\UpdateOrganisationRequest;
use App\Models\Organisation;
use App\Services\Organisations\ManagementService;
use App\Services\Organisations\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganisationController extends Controller
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
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Organisation::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only(['search', 'trashed', 'sort_by', 'sort_direction', 'per_page'])
        );

        return Inertia::render('Organisations/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Organisation::class);

        return Inertia::render('Organisations/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrganisationRequest $request): JsonResponse|RedirectResponse
    {
        $organisation = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($organisation, 201);
        }

        return redirect()->route('organisations.show', $organisation->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id): Response
    {
        $organisation = Organisation::findOrFail($id);
        $this->authorize('view', $organisation);

        $data = $this->query->getById($request->user(), $id);

        return Inertia::render('Organisations/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, int $id): Response
    {
        $organisation = Organisation::findOrFail($id);
        $this->authorize('update', $organisation);

        $data = $this->query->getById($request->user(), $id);

        return Inertia::render('Organisations/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrganisationRequest $request, Organisation $organisation): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $organisation);

        $updated = $this->management->update($request, $organisation);

        if ($request->wantsJson()) {
            return response()->json($updated);
        }

        return redirect()->route('organisations.show', $updated->id);
    }

    /**
     * Soft delete the specified resource.
     */
    public function destroy(Request $request, Organisation $organisation): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $organisation);

        $this->management->destroy($organisation, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('organisations.index');
    }

    /**
     * Restore a soft-deleted resource.
     */
    public function restore(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $organisation = Organisation::withTrashed()->findOrFail($id);
        $this->authorize('restore', $organisation);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('organisations.index');
    }

    /**
     * Permanently delete the specified resource.
     */
    public function forceDelete(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $organisation = Organisation::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $organisation);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('organisations.index');
    }

    /**
     * Bulk soft delete multiple organisations.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer'],
        ]);

        $result = $this->management->bulkDelete(
            $request->input('ids'),
            $request->user(),
            fn (Organisation $organisation) => $this->authorize('delete', $organisation)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('organisations.index');
    }

    /**
     * Bulk restore multiple organisations.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer'],
        ]);

        $result = $this->management->bulkRestore(
            $request->input('ids'),
            $request->user(),
            fn (Organisation $organisation) => $this->authorize('restore', $organisation)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('organisations.index');
    }
}

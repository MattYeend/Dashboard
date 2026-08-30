<?php

namespace App\Http\Controllers;

use App\Http\Requests\Permissions\AssignPermissionRolesRequest;
use App\Http\Requests\Permissions\StorePermissionRequest;
use App\Http\Requests\Permissions\UpdatePermissionRequest;
use App\Models\Permission;
use App\Services\Permissions\ManagementService;
use App\Services\Permissions\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
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
     * Passes paginated permissions to the Permissions/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Permission::class);

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

        return Inertia::render('Permissions/Index', $data);
    }

    /**
     * Show the form for creating a new permission.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Permission::class);

        return Inertia::render('Permissions/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StorePermissionRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(StorePermissionRequest $request): JsonResponse|RedirectResponse
    {
        $permission = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($permission, 201);
        }

        return redirect()->route('permissions.show', $permission->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single permission to the Permissions/Show Inertia page.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        int $id,
        Request $request
    ): Response {
        $permission = Permission::withTrashed()->findOrFail($id);

        $this->authorize('view', $permission);

        $data = $this->query->getById(
            $request->user(),
            $permission->id,
            withTrashed: true,
        );

        return Inertia::render('Permissions/Show', $data);
    }

    /**
     * Show the form for editing an existing permission.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        int $id,
        Request $request
    ): Response {
        $permission = Permission::findOrFail($id);

        $this->authorize('update', $permission);

        $data = $this->query->getById(
            $request->user(),
            $permission->id
        );

        return Inertia::render('Permissions/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdatePermissionRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdatePermissionRequest $request,
        Permission $permission
    ): JsonResponse|RedirectResponse {
        $permission = $this->management->update($request, $permission);

        if ($request->wantsJson()) {
            return response()->json($permission);
        }

        return redirect()->route('permissions.show', $permission->id);
    }

    /**
     * Sync the roles assigned to the specified permission.
     *
     * Validation is handled upstream by AssignPermissionRolesRequest, which
     * also implicitly authorises the operation via its authorize() method.
     */
    public function assignRoles(
        AssignPermissionRolesRequest $request,
        Permission $permission
    ): JsonResponse|RedirectResponse {
        $permission = $this->management->assignRoles($request, $permission);

        if ($request->wantsJson()) {
            return response()->json($permission);
        }

        return redirect()->route('permissions.edit', $permission->id)->with('success', 'Roles assigned.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * permission instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Permission $permission
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $permission);

        $this->management->destroy($permission, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('permissions.index');
    }

    /**
     * Restore a soft-deleted permission.
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
        $permission = Permission::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $permission);

        $this->management->restore($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('permissions.index');
    }

    /**
     * Permanently delete a soft-deleted permission.
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
        $permission = Permission::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $permission);

        $this->management->forceDelete($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('permissions.index');
    }

    /**
     * Bulk soft-delete multiple permissions.
     *
     * Authorises each permission individually via the 'delete' policy.
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
            fn (Permission $permission) => $this->authorize('delete', $permission)
        );

        if (request()->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('permissions.index');
    }

    /**
     * Bulk restore multiple soft-deleted permissions.
     *
     * Authorises each permission individually via the 'restore' policy.
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
            fn (Permission $permission) => $this->authorize('restore', $permission)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('permissions.index');
    }
}

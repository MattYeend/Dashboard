<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\Users\ManagementService;
use App\Services\Users\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        protected ManagementService $management,
        protected QueryService $query,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * Passes paginated users to the Users/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $users = $this->query->getPaginated($request->all());

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new user.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Users/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreUserRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->management->store($request);

        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single user to the Users/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(User $user): Response
    {
        $this->authorize('view', $user);
        $this->authorize('access', $user);

        $user = $this->query->getById($user->id);

        return Inertia::render('Users/Show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing an existing user.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        $user = $this->query->getById($user->id);

        return Inertia::render('Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateUserRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateUserRequest $request,
        User $user
    ): JsonResponse {
        $user = $this->management->update($request, $user);

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * user instance is still fully accessible during logging.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->management->destroy($user);

        return response()->json(null, 204);
    }

    /**
     * Restore a soft-deleted user.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'restore' policy before proceeding.
     */
    public function restore(int $id): JsonResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $user);

        $this->management->restore($id);

        return response()->json(null, 204);
    }

    /**
     * Permanently delete a soft-deleted user.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(int $id): JsonResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $user);

        $this->management->forceDelete($id);

        return response()->json(null, 204);
    }

    /**
     * Bulk soft-delete multiple users.
     *
     * Authorises each user individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:users,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (User $user) => $this->authorize('delete', $user)
        );

        return response()->json(null, 204);
    }

    /**
     * Bulk restore multiple soft-deleted users.
     *
     * Authorises each user individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:users,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkRestore(
            $ids,
            $actor,
            fn (User $user) => $this->authorize('restore', $user)
        );

        return response()->json(null, 204);
    }
}

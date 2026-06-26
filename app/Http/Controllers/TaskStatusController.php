<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskStatusRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\TaskStatus;
use App\Services\TaskStatuses\ManagementService;
use App\Services\TaskStatuses\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskStatusController extends Controller
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
     * Passes paginated contacts to the Contacts/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', TaskStatus::class);

        $taskStatuses = $this->query->getPaginated(
            request()->only(['search', 'sort_by', 'sort_direction', 'trashed', 'per_page'])
        );

        return Inertia::render('TaskStatuses/Index', $taskStatuses);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', TaskStatus::class);

        return Inertia::render('TaskStatuses/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreContactRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(
        StoreTaskStatusRequest $request
    ): JsonResponse|RedirectResponse {
        $taskStatus = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($taskStatus, 201);
        }

        return redirect()->route('TaskStatus.index', $taskStatus->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single taskStatus to the Contacts/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(TaskStatus $taskStatus): Response
    {
        $this->authorize('view', $taskStatus);

        $taskStatus = $this->query->getById($taskStatus->id);

        return Inertia::render('TaskStatuses/Show', $taskStatus);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(TaskStatus $taskStatus): Response
    {
        $this->authorize('update', $taskStatus);

        $taskStatus = $this->query->getById($taskStatus->id);

        return Inertia::render('TaskStatuses/Edit', $taskStatus);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateTaskStatusRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateTaskStatusRequest $request,
        TaskStatus $taskStatus
    ): JsonResponse|RedirectResponse {
        $taskStatus = $this->management->update($request, $taskStatus);

        if ($request->wantsJson()) {
            return response()->json($taskStatus);
        }

        return redirect()->route('TaskStatus.index', $taskStatus->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * taskStatus instance is still fully accessible during logging.
     */
    public function destroy(TaskStatus $taskStatus): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $taskStatus);

        $this->management->destroy($taskStatus);

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('TaskStatus.index');
    }

    /**
     * Restore a soft-deleted taskStatus.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'restore' policy before proceeding.
     */
    public function restore(int $id): JsonResponse|RedirectResponse
    {
        $taskStatus = TaskStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $taskStatus);

        $this->management->restore($id);

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('TaskStatus.index');
    }

    /**
     * Permanently delete a soft-deleted taskStatus.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(int $id): JsonResponse|RedirectResponse
    {
        $taskStatus = TaskStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $taskStatus);

        $this->management->forceDelete($id);

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('TaskStatus.index');
    }

    /**
     * Bulk soft-delete multiple contacts.
     *
     * Authorises each task status individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:task_statuses,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (TaskStatus $taskStatus) => $this->authorize('delete', $taskStatus)
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('TaskStatus.index');
    }

    /**
     * Bulk restore multiple soft-deleted contacts.
     *
     * Authorises each task status individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:task_statuses,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkRestore(
            $ids,
            $actor,
            fn (TaskStatus $taskStatus) => $this->authorize('restore', $taskStatus)
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('TaskStatus.index');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Services\Tasks\ManagementService;
use App\Services\Tasks\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        private QueryService $query,
        private ManagementService $management,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * Passes paginated tasks to the Tasks/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Task::class);

        $data = $this->query->getPaginated(
            request()->only(['search', 'sort_by', 'sort_direction', 'trashed', 'per_page', 'status_id', 'assigned_to'])
        );

        return Inertia::render('Tasks/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Task::class);

        return Inertia::render('Tasks/Create', $this->query->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreTaskRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->management->store($request);

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single task to the Tasks/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(Task $task): Response
    {
        $this->authorize('view', $task);
        $this->authorize('access', $task);

        $data = $this->query->getById($task->id);

        return Inertia::render('Tasks/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(Task $task): Response
    {
        $this->authorize('update', $task);

        $data = array_merge(
            $this->query->getById($task->id),
            $this->query->getFormData()
        );

        return Inertia::render('Tasks/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateTaskRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $task = $this->management->update($request, $task);

        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * task instance is still fully accessible during logging.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->management->destroy($task);

        return response()->json(null, 204);
    }

    /**
     * Restore a soft-deleted task.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'restore' policy before proceeding.
     */
    public function restore(int $id): JsonResponse
    {
        $task = Task::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $task);

        $this->management->restore($id);

        return response()->json(null, 204);
    }

    /**
     * Permanently delete a soft-deleted task.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(int $id): JsonResponse
    {
        $task = Task::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $task);

        $this->management->forceDelete($id);

        return response()->json(null, 204);
    }

    /**
     * Bulk soft-delete multiple tasks.
     *
     * Authorises each task individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:tasks,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (Task $task) => $this->authorize('delete', $task)
        );

        return response()->json(null, 204);
    }

    /**
     * Bulk restore multiple soft-deleted tasks.
     *
     * Authorises each task individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:tasks,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkRestore(
            $ids,
            $actor,
            fn (Task $task) => $this->authorize('restore', $task)
        );

        return response()->json(null, 204);
    }
}

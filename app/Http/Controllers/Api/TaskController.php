<?php

namespace App\Http\Controllers\Api;

use App\Enums\TokenAbility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\Tasks\CreatorService;
use App\Services\Tasks\DeleterService;
use App\Services\Tasks\QueryService;
use App\Services\Tasks\UpdaterService;
use App\Traits\AuthorisesTokenAbility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use AuthorisesTokenAbility;

    public function __construct(
        private readonly QueryService $queryService,
        private readonly CreatorService $creatorService,
        private readonly UpdaterService $updaterService,
        private readonly DeleterService $deleterService,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * Returns paginated tasks, already formatted by the QueryService,
     * as a raw JSON response (not a resource collection, since getPaginated
     * returns a pre-shaped array rather than an Eloquent collection).
     *
     * Authorises via the 'viewAny' policy, then confirms the request's token carries the
     * 'tasks:read' ability before returning data.
     */
    public function index(
        Request $request
    ): JsonResponse {
        $this->authorize('viewAny', Task::class);
        $this->authoriseTokenAbility(
            $request,
            TokenAbility::TasksRead->value
        );

        $data = $this->queryService->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return response()->json($data);
    }

    /**
     * Store a newly created resource.
     *
     * Creates a task from validated request data via the CreatorService,
     * passing the authenticated user's ID as the acting creator.
     *
     * Authorises via the 'create' policy, then confirms the request's token carries the
     * 'tasks:write' ability before persisting.
     */
    public function store(
        StoreTaskRequest $request
    ): TaskResource {
        $this->authorize('create', Task::class);
        $this->authoriseTokenAbility(
            $request,
            TokenAbility::TasksWrite->value
        );

        $task = $this->creatorService->create(
            $request->validated(),
            $request->user()->id,
        );

        return new TaskResource($task);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single task via the QueryService, which includes formatted
     * relations and permissions metadata, as a raw JSON response.
     *
     * Authorises via the 'view' policy, then confirms the request's token carries the
     * 'tasks:read' ability before returning data.
     */
    public function show(
        Request $request,
        Task $task
    ): JsonResponse {
        $this->authorize('view', $task);
        $this->authoriseTokenAbility(
            $request,
            TokenAbility::TasksRead->value
        );

        return response()->json(
            $this->queryService->getById(
                $request->user(),
                $task->id
            )
        );
    }

    /**
     * Update the specified resource.
     *
     * Updates a task from validated request data via the UpdaterService,
     * passing the authenticated user's ID as the acting updater.
     *
     * Authorises via the 'update' policy, then confirms the request's token carries the
     * 'tasks:write' ability before persisting.
     */
    public function update(
        UpdateTaskRequest $request,
        Task $task
    ): TaskResource {
        $this->authorize('update', $task);
        $this->authoriseTokenAbility(
            $request,
            TokenAbility::TasksWrite->value
        );

        $updated = $this->updaterService->update(
            $task,
            $request->validated(),
            $request->user()->id,
        );

        return new TaskResource($updated);
    }

    /**
     * Remove the specified resource.
     *
     * Soft-deletes a task via the DeleterService, passing the authenticated
     * user's ID as the acting deleter, and returns an empty 204 response.
     *
     * Authorises via the 'delete' policy, then confirms the request's token carries the
     * 'tasks:write' ability before deleting.
     */
    public function destroy(
        Request $request,
        Task $task
    ): JsonResponse {
        $this->authorize('delete', $task);
        $this->authoriseTokenAbility(
            $request,
            TokenAbility::TasksWrite->value
        );

        $this->deleterService->delete(
            $task,
            $request->user()->id
        );

        return response()->json(null, 204);
    }
}

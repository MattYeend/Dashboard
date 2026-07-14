<?php

namespace App\Http\Controllers\Api;

use App\Enums\TokenAbility;
use App\Traits\AuthorisesTokenAbility;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\Tasks\CreatorService;
use App\Services\Tasks\DeleterService;
use App\Services\Tasks\QueryService;
use App\Services\Tasks\UpdaterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
     * Returns paginated tasks as a JSON resource collection.
     *
     * Authorises via the 'viewAny' policy, then confirms the request's token carries the
     * 'tasks:read' ability before returning data.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Task::class);
        $this->authoriseTokenAbility($request, TokenAbility::TasksRead->value);

        return TaskResource::collection($this->queryService->paginate($request));
    }

    /**
     * Store a newly created resource.
     *
     * Creates a task from validated request data via the CreatorService.
     *
     * Authorises via the 'create' policy, then confirms the request's token carries the
     * 'tasks:write' ability before persisting.
     */
    public function store(StoreTaskRequest $request): TaskResource
    {
        $this->authorize('create', Task::class);
        $this->authoriseTokenAbility($request, TokenAbility::TasksWrite->value);

        return new TaskResource($this->creatorService->create($request->validated()));
    }

    /**
     * Display the specified resource.
     *
     * Returns a single task as a JSON resource.
     *
     * Authorises via the 'view' policy, then confirms the request's token carries the
     * 'tasks:read' ability before returning data.
     */
    public function show(Request $request, Task $task): TaskResource
    {
        $this->authorize('view', $task);
        $this->authoriseTokenAbility($request, TokenAbility::TasksRead->value);

        return new TaskResource($task);
    }

    /**
     * Update the specified resource.
     *
     * Updates a task from validated request data via the UpdaterService.
     *
     * Authorises via the 'update' policy, then confirms the request's token carries the
     * 'tasks:write' ability before persisting.
     */
    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $this->authorize('update', $task);
        $this->authoriseTokenAbility($request, TokenAbility::TasksWrite->value);

        return new TaskResource($this->updaterService->update($task, $request->validated()));
    }

    /**
     * Remove the specified resource.
     *
     * Soft-deletes a task via the DeleterService and returns an empty 204 response.
     *
     * Authorises via the 'delete' policy, then confirms the request's token carries the
     * 'tasks:write' ability before deleting.
     */
    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->authorize('delete', $task);
        $this->authoriseTokenAbility($request, TokenAbility::TasksWrite->value);

        $this->deleterService->delete($task);

        return response()->json(null, 204);
    }
}

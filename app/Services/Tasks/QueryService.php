<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Services\TrashFilterService;
use Illuminate\Database\Eloquent\Builder;

class QueryService
{
    /**
     * Inject the required services into the query service.
     */
    public function __construct(
        protected readonly SortingService $sortingService,
        protected readonly TrashFilterService $trashFilterService,
        protected readonly FilterService $filterService,
        protected readonly FormatterService $formatterService
    ) {}

    /**
     * Get paginated tasks with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPaginated(
        User $user,
        array $filters = []
    ): array {
        $query = $this->buildQuery($filters);
        $paginated = $this->paginate(
            $query,
            min((int) ($filters['per_page'] ?? 15), 100)
        );

        return array_merge(
            $paginated,
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get a single task by ID.
     *
     * @return array<string, mixed>
     */
    public function getById(
        User $user,
        int $id,
        bool $withTrashed = false
    ): array {
        $task = $this->findTask($id, $withTrashed);

        return array_merge(
            ['task' => $this->formatterService->format($task)],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get data needed to populate create and edit forms.
     *
     * @return array<string, mixed>
     */
    public function getFormData(): array
    {
        return [
            'statuses' => TaskStatus::orderBy('title')->get(['id', 'title', 'background_colour', 'text_colour']),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Task>
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Task::query()->with(['assignee', 'status']);
        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as a plain array.
     *
     * @param  Builder<Task>  $query
     * @return array<string, mixed>
     */
    protected function paginate(Builder $query, int $perPage): array
    {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'tasks' => [
                'data' => array_map(
                    fn (Task $task) => $this->formatterService->format($task),
                    $paginator->items()
                ),
                'links' => $paginator->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
        ];
    }

    /**
     * Get user permissions for the authenticated user.
     *
     * @return array<string, mixed>
     */
    protected function getPermissions(User $user): array
    {
        if (! $user) {
            return ['permissions_meta' => []];
        }

        return [
            'permissions_meta' => [
                'can_create' => $user->can('create', Task::class),
                'can_view_any' => $user->can('viewAny', Task::class),
            ],
        ];
    }

    /**
     * Get base data for the view.
     *
     * @return array<string, mixed>
     */
    protected function baseData(): array
    {
        return [
            'sort_fields' => $this->sortingService->getAvailableSortFields(),
            'trash_filters' => $this->trashFilterService->getFilterOptions(),
        ];
    }

    /**
     * Find a task by ID with optional trashed records.
     */
    private function findTask(int $id, bool $withTrashed = false): Task
    {
        $query = Task::query()->with(['assignee', 'status']);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    /**
     * Apply sorting and trash filtering to the query.
     *
     * @param  Builder<Task>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Task>
     */
    private function applySorting(Builder $query, array $filters): Builder
    {
        $query = $this->trashFilterService->applyFilter(
            $query,
            $filters['trashed'] ?? null
        );

        return $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'due_date',
            $filters['sort_direction'] ?? 'asc'
        );
    }
}

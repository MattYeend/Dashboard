<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Services\TrashFilterService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
     */
    public function getFormData(): array
    {
        return [
            'statuses' => TaskStatus::orderBy('title')->get([
                'id',
                'title',
                'background_colour',
                'text_colour',
            ]),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * Get tasks whose due date or assigned date falls within the given range.
     */
    public function forDateRange(string $start, string $end): Collection
    {
        return Task::query()
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('due_date', [$start, $end])
                    ->orWhereBetween('assigned_date', [$start, $end]);
            })
            ->with(['assignee', 'status'])
            ->get();
    }

    /**
     * Build the base query with filters.
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Task::query()->with([
            'assignee',
            'status',
            'tags',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);
        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as a plain array.
     */
    protected function paginate(
        Builder $query,
        int $perPage
    ): array {
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
     */
    protected function getPermissions(User $user): array
    {
        return [
            'permissions_meta' => [
                'can_create' => $user->can('create', Task::class),
                'can_view_any' => $user->can('viewAny', Task::class),
                'can_export' => $user->can('export', Task::class),
            ],
        ];
    }

    /**
     * Get base data for the view.
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
    private function findTask(
        int $id,
        bool $withTrashed = false
    ): Task {
        $query = Task::query()->with([
            'assignee',
            'status',
            'tags',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    /**
     * Apply sorting and trash filtering to the query.
     */
    private function applySorting(
        Builder $query,
        array $filters
    ): Builder {
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

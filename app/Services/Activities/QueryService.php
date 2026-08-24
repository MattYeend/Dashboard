<?php

namespace App\Services\Activities;

use App\Models\Activity;
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
        protected readonly FormatterService $formatterService,
        protected readonly ActivityableTypeRegistryService $registry,
    ) {}

    /**
     * Get paginated activities for a single activityable record, with
     * filters. Matches the (User $actor, array $filters) call shape used
     * by every other module's QueryService::getPaginated().
     */
    public function getPaginated(
        User $actor,
        array $filters = []
    ): array {
        $query = $this->buildQuery($filters);
        $paginated = $this->paginate(
            $query,
            min((int) ($filters['per_page'] ?? 25), 100)
        );

        return array_merge(
            $paginated,
            $this->getPermissions($actor),
            $this->baseData(),
        );
    }

    /**
     * Build the base query with filters.
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Activity::query()
            ->with(['creator', 'updater', 'deleter', 'restorer'])
            ->where('activityable_type', $filters['activityable_type'] ?? null)
            ->where('activityable_id', $filters['activityable_id'] ?? null);

        $query = $this->filterService->applyAll($query, $filters);
        $query = $this->trashFilterService->applyFilter($query, $filters['trashed'] ?? null);

        return $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'occurred_at',
            $filters['sort_direction'] ?? 'desc'
        );
    }

    /**
     * Paginate the query and return as plain array.
     */
    protected function paginate(
        Builder $query,
        int $perPage
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'activities' => [
                'data' => array_map(
                    fn (Activity $activity) => $this->formatterService->format($activity),
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
                'can_create' => $user->can('create', Activity::class),
                'can_view_any' => $user->can('viewAny', Activity::class),
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
            'activityableTypes' => $this->registry->types(),
        ];
    }

    /**
     * Unscoped query across every activityable record — admin-only export.
     */
    public function forExportAll(array $filters = []): Builder
    {
        $query = Activity::query()->with(['creator', 'activityable']);

        $query = $this->filterService->applyAll($query, $filters);

        return $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'occurred_at',
            $filters['sort_direction'] ?? 'desc'
        );
    }
}

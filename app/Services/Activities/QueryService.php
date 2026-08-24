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
     * filters.
     *
     * The activityable_type supplied by the client is a short registry key
     * such as "company". The database stores the fully-qualified model
     * class name, such as App\Models\Company.
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
            ->with([
                'creator',
                'updater',
                'deleter',
                'restorer',
            ]);

        /*
         * Resolve the UI-facing activityable type into the actual model
         * class stored in the database.
         */
        if (isset($filters['activityable_type'])) {
            $activityableType = $this->registry->modelClassForKey(
                $filters['activityable_type']
            );

            /*
             * An unknown activityable type should return no results rather
             * than querying with an untrusted/raw class name.
             */
            if ($activityableType === null) {
                $query->whereRaw('1 = 0');

                return $query;
            }

            $query->where('activityable_type', $activityableType);
        }

        /*
         * Only apply the ID restriction when one was actually supplied.
         */
        if (isset($filters['activityable_id'])) {
            $query->where(
                'activityable_id',
                $filters['activityable_id']
            );
        }

        $query = $this->filterService->applyAll($query, $filters);

        /*
         * Eloquent's normal query already excludes soft-deleted records.
         * TrashFilterService controls whether normal, trashed or all
         * records should be returned.
         */
        $query = $this->trashFilterService->applyFilter(
            $query,
            $filters['trashed'] ?? null
        );

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
        $paginator = $query
            ->paginate($perPage)
            ->withQueryString();

        return [
            'activities' => [
                'data' => array_map(
                    fn (Activity $activity) => $this->formatterService->format($activity),
                    $paginator->items()
                ),

                'links' => $paginator
                    ->linkCollection()
                    ->toArray(),

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
                'can_create' => $user->can(
                    'create',
                    Activity::class
                ),

                'can_view_any' => $user->can(
                    'viewAny',
                    Activity::class
                ),
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
     * Unscoped query across every activityable record.
     *
     * Used by the admin-only export.
     */
    public function forExportAll(array $filters = []): Builder
    {
        $query = Activity::query()
            ->with([
                'creator',
                'activityable',
            ]);

        /*
         * Export may be scoped to an activityable record.
         * Resolve the UI key to the stored FQCN here as well.
         */
        if (isset($filters['activityable_type'])) {
            $activityableType = $this->registry->modelClassForKey(
                $filters['activityable_type']
            );

            if ($activityableType === null) {
                $query->whereRaw('1 = 0');

                return $query;
            }

            $query->where(
                'activityable_type',
                $activityableType
            );
        }

        if (isset($filters['activityable_id'])) {
            $query->where(
                'activityable_id',
                $filters['activityable_id']
            );
        }

        $query = $this->filterService->applyAll(
            $query,
            $filters
        );

        $query = $this->trashFilterService->applyFilter(
            $query,
            $filters['trashed'] ?? null
        );

        return $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'occurred_at',
            $filters['sort_direction'] ?? 'desc'
        );
    }
}

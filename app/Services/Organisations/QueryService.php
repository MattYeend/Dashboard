<?php

namespace App\Services\Organisations;

use App\Models\Organisation;
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
     * Get paginated organisations with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPaginated(User $user, array $filters = []): array
    {
        $query = $this->buildQuery($filters);
        $paginated = $this->paginate($query, min((int) ($filters['per_page'] ?? 15), 100));

        return array_merge(
            $paginated,
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get a single organisation by ID.
     *
     * @return array<string, mixed>
     */
    public function getById(User $user, int $id, bool $withTrashed = false): array
    {
        $organisation = $this->findOrganisation($id, $withTrashed);

        return array_merge(
            ['organisation' => $this->formatterService->format($organisation)],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Organisation>
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Organisation::query()
            ->withCount('users')
            ->with(['creator', 'updater', 'deleter', 'restorer']);

        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as a plain array.
     *
     * @param  Builder<Organisation>  $query
     * @return array<string, mixed>
     */
    protected function paginate(Builder $query, int $perPage): array
    {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'organisations' => [
                'data' => array_map(
                    fn (Organisation $organisation) => $this->formatterService->format($organisation),
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
        return [
            'permissions_meta' => [
                'can_create' => $user->can('create', Organisation::class),
                'can_view_any' => $user->can('viewAny', Organisation::class),
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
     * Find an organisation by ID with optional trashed records.
     */
    private function findOrganisation(int $id, bool $withTrashed = false): Organisation
    {
        $query = Organisation::query()->with(['creator', 'updater', 'deleter', 'restorer']);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    /**
     * Apply sorting and trash filtering to the query.
     *
     * @param  Builder<Organisation>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Organisation>
     */
    private function applySorting(Builder $query, array $filters): Builder
    {
        $query = $this->trashFilterService->applyFilter($query, $filters['trashed'] ?? null);

        return $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'name',
            $filters['sort_direction'] ?? 'asc'
        );
    }
}

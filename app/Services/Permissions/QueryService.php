<?php

namespace App\Services\Permissions;

use App\Models\Permission;
use App\Models\User;
use App\Services\TrashFilterService;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

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
     * Get paginated permissions with filters.
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
     * Get a single permission by ID.
     *
     * @return array<string, mixed>
     */
    public function getById(
        User $user,
        int $id,
        bool $withTrashed = false
    ): array {
        $permission = $this->findPermission($id, $withTrashed);

        return array_merge(
            ['permission' => $this->formatterService->format($permission)],
            $this->getFormData(),
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
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * Get the permission × role assignment matrix data.
     *
     * @return array<string, mixed>
     */
    public function getMatrixData(): array
    {
        $permissions = Permission::query()
            ->with('roles:id')
            ->orderBy('name')
            ->get()
            ->map(fn (Permission $permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
                'role_ids' => $permission->roles->pluck('id')->all(),
            ]);

        return [
            'permissions' => $permissions,
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Permission>
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Permission::query()->with([
            'creator',
            'updater',
            'deleter',
            'restorer',
            'roles',
        ]);
        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as a plain array.
     *
     * @param  Builder<Permission>  $query
     * @return array<string, mixed>
     */
    protected function paginate(
        Builder $query,
        int $perPage
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'permissions' => [
                'data' => array_map(
                    fn (Permission $permission) => $this->formatterService->format($permission),
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
                'can_create' => $user->can('create', Permission::class),
                'can_view_any' => $user->can('viewAny', Permission::class),
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
     * Find a permission by ID with optional trashed records.
     */
    private function findPermission(
        int $id,
        bool $withTrashed = false
    ): Permission {
        $query = Permission::query()->with([
            'creator',
            'updater',
            'deleter',
            'restorer',
            'roles',
        ]);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    /**
     * Apply sorting and trash filtering to the query.
     *
     * @param  Builder<Permission>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Permission>
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
            $filters['sort_by'] ?? 'name',
            $filters['sort_direction'] ?? 'asc'
        );
    }
}

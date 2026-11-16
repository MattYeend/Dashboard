<?php

declare(strict_types=1);

namespace App\Services\Categories;

use App\Models\Category;
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
     * Get paginated categories with filters.
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
            max(1, min((int) ($filters['per_page'] ?? 15), 100))
        );

        return array_merge(
            $paginated,
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get a single category by ID.
     *
     * @return array<string, mixed>
     */
    public function getById(User $user, Category $category): array
    {
        $category->loadMissing([
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        return array_merge(
            ['category' => $this->formatterService->format($category)],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get data needed to populate create and edit forms.
     *
     * @return array<string, mixed>
     */
    public function getFormData(?int $excludeId = null): array
    {
        return [
            'parentOptions' => $this->getParentOptions($excludeId),
        ];
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Category>
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Category::query()->with([
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
     *
     * @param  Builder<Category>  $query
     * @return array<string, mixed>
     */
    protected function paginate(
        Builder $query,
        int $perPage
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'categories' => [
                'data' => array_map(
                    fn (Category $category) => $this->formatterService->format($category),
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
                'can_create' => $user->can('create', Category::class),
                'can_view_any' => $user->can('viewAny', Category::class),
                'can_export' => $user->can('export', Category::class),
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
     * Apply sorting and trash filtering to the query.
     *
     * @param  Builder<Category>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Category>
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

    /**
     * Get the list of categories eligible to be selected as a parent.
     *
     * Excludes the given category itself, and its descendants, to prevent
     * a circular parent/child hierarchy.
     *
     * @return array<int, array{value: int, label: string}>
     */
    private function getParentOptions(?int $excludeId = null): array
    {
        $query = Category::query()->orderBy('name');

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId)
                ->whereNotIn('id', $this->descendantIds($excludeId));
        }

        return $query->get(['id', 'name'])
            ->map(fn (Category $category) => [
                'value' => $category->id,
                'label' => $category->name,
            ])->all();
    }

    /**
     * Recursively resolve all descendant category IDs for the given category.
     *
     * @return array<int, int>
     */
    private function descendantIds(int $id): array
    {
        $childIds = Category::query()->where('parent_id', $id)->pluck('id')->all();

        $descendants = $childIds;

        foreach ($childIds as $childId) {
            $descendants = [...$descendants, ...$this->descendantIds($childId)];
        }

        return $descendants;
    }
}

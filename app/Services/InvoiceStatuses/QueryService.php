<?php

namespace App\Services\InvoiceStatuses;

use App\Models\InvoiceStatus;
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
     * Get paginated task statuses with filters.
     */
    public function getPaginated(
        User $actor,
        array $filters = []
    ): array {
        $query = $this->buildQuery($filters);
        $paginated = $this->paginate(
            $query,
            min((int) ($filters['per_page'] ?? 15), 100)
        );

        return array_merge(
            $paginated,
            $this->getPermissions($actor),
            $this->baseData(),
        );
    }

    /**
     * Get a single invoiceStatus by ID.
     */
    public function getById(
        User $user,
        int $id,
        bool $withTrashed = false
    ): array {
        $invoiceStatus = $this->findTaskStatus($id, $withTrashed);

        return array_merge(
            ['invoiceStatus' => $this->formatterService->format($invoiceStatus)],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Build the base query with filters.
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = InvoiceStatus::query();
        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as plain array.
     */
    protected function paginate(Builder $query, int $perPage): array
    {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'invoiceStatuses' => [
                'data' => array_map(
                    fn (InvoiceStatus $invoiceStatus) => $this->formatterService->format($invoiceStatus),
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
        if (! $user) {
            return ['permissions_meta' => []];
        }

        return [
            'permissions_meta' => [
                'can_create' => $user->can('create', InvoiceStatus::class),
                'can_view_any' => $user->can('viewAny', InvoiceStatus::class),
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
     * Find a invoiceStatus by ID with optional trashed records.
     */
    private function findTaskStatus(
        int $id,
        bool $withTrashed = false
    ): InvoiceStatus {
        $query = InvoiceStatus::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting(Builder $query, array $filters): Builder
    {
        $query = $this->trashFilterService->applyFilter(
            $query,
            $filters['trashed'] ?? null
        );

        return $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'title',
            $filters['sort_direction'] ?? 'asc'
        );
    }
}

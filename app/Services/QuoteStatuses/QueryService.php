<?php

declare(strict_types=1);

namespace App\Services\QuoteStatuses;

use App\Models\QuoteStatus;
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
    ) {}

    /**
     * Get paginated quote statuses with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPaginated(
        User $actor,
        array $filters = []
    ): array {
        $query = $this->buildQuery($filters);
        $paginated = $this->paginate(
            $query,
            max(1, min((int) ($filters['per_page'] ?? 15), 100))
        );

        return array_merge(
            $paginated,
            $this->getPermissions($actor),
            $this->baseData(),
        );
    }

    /**
     * Get a single quote status by ID.
     *
     * @return array<string, mixed>
     */
    public function getById(User $user, QuoteStatus $quoteStatus): array
    {
        $quoteStatus->loadMissing([
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        return array_merge(
            ['quoteStatus' => $this->formatterService->format($quoteStatus)],
            $this->getFormData(),
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get the data needed to render the Create/Edit quote status form.
     *
     * Quote statuses need no extra form data.
     *
     * @return array<string, mixed>
     */
    public function getFormData(): array
    {
        return [];
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<QuoteStatus>
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = QuoteStatus::query()->with([
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as plain array.
     *
     * @param  Builder<QuoteStatus>  $query
     * @return array<string, mixed>
     */
    protected function paginate(
        Builder $query,
        int $perPage
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'quoteStatuses' => [
                'data' => array_map(
                    fn (QuoteStatus $quoteStatus) => $this->formatterService->format($quoteStatus),
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
                'can_create' => $user->can('create', QuoteStatus::class),
                'can_view_any' => $user->can('viewAny', QuoteStatus::class),
                'can_export' => $user->can('export', QuoteStatus::class),
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
     * Apply trash filtering and sorting to the query.
     *
     * @param  Builder<QuoteStatus>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<QuoteStatus>
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
            $filters['sort_by'] ?? 'title',
            $filters['sort_direction'] ?? 'asc'
        );
    }
}
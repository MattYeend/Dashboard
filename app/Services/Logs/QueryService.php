<?php

namespace App\Services\Logs;

use App\Models\Log;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class QueryService
{
    /**
     * Inject the required services into the query service.
     */
    public function __construct(
        protected readonly SortingService $sortingService,
        protected readonly FilterService $filterService,
        protected readonly FormatterService $formatterService
    ) {}

    /**
     * Get paginated activity logs with filters.
     */
    public function getPaginated(User $actor, array $filters = []): array
    {
        $query = $this->buildQuery($filters);
        $paginated = $this->paginate($query, min((int) ($filters['per_page'] ?? 15), 100));

        return array_merge(
            $paginated,
            $this->getPermissions($actor),
            $this->baseData(),
            ['filters' => $filters],
        );
    }

    /**
     * Build the base query with filters and sorting applied.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Log>
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Log::query()->with(['loggedInUser', 'relatedToUser']);
        $query = $this->filterService->applyAll($query, $filters);

        return $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'created_at',
            $filters['sort_direction'] ?? 'desc'
        );
    }

    /**
     * Paginate the query and return as plain array.
     *
     * @param  Builder<Log>  $query
     */
    protected function paginate(Builder $query, int $perPage): array
    {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'logs' => [
                'data' => array_map(
                    fn (Log $log) => $this->formatterService->format($log),
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
                'can_view_any' => $user->can('viewAny', Log::class),
                'can_export' => $user->can('export', Log::class),
                'can_delete' => $user->can('delete activity logs'),
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
            'action_options' => Log::actionLabels(),
        ];
    }
}

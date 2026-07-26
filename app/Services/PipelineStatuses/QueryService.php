<?php

namespace App\Services\PipelineStatuses;

use App\Models\PipelineStatus;
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
     * Get paginated pipeline statuses with filters.
     */
    public function getPaginated(
        User $actor,
        array $filters = []
    ): array {
        $query = $this->buildQuery(
            $filters
        );
        $paginated = $this->paginate(
            $query,
            min((int) ($filters['per_page'] ?? 15), 100)
        );

        return array_merge(
            $paginated,
            $this->getPermissions(
                $actor
            ),
            $this->baseData(),
        );
    }

    /**
     * Get a single pipelineStatus by ID.
     */
    public function getById(
        User $user,
        int $id,
        bool $withTrashed = false
    ): array {
        $pipelineStatus = $this->findPipelineStatus(
            $id,
            $withTrashed
        );

        return array_merge(
            [
                'pipelineStatus' => $this->formatterService->format(
                    $pipelineStatus
                ),
            ],
            $this->getPermissions(
                $user
            ),
            $this->baseData(),
        );
    }

    /**
     * Build the base query with filters.
     */
    protected function buildQuery(
        array $filters
    ): Builder {
        $query = PipelineStatus::query();
        $query = $this->filterService->applyAll(
            $query,
            $filters
        );

        return $this->applySorting(
            $query,
            $filters
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
            'pipelineStatuses' => [
                'data' => array_map(
                    fn (PipelineStatus $pipelineStatus) => $this->formatterService->format(
                        $pipelineStatus
                    ),
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
                'can_create' => $user->can(
                    'create',
                    PipelineStatus::class
                ),
                'can_view_any' => $user->can(
                    'viewAny',
                    PipelineStatus::class
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
        ];
    }

    /**
     * Find a pipelineStatus by ID with optional trashed records.
     */
    private function findPipelineStatus(
        int $id,
        bool $withTrashed = false
    ): PipelineStatus {
        $query = PipelineStatus::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    /**
     * Apply sorting to the query.
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

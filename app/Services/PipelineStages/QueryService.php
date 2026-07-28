<?php

namespace App\Services\PipelineStages;

use App\Models\Pipeline;
use App\Models\PipelineStage;
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
     * Get paginated pipeline stages, scoped to a pipeline, with filters.
     */
    public function getPaginated(
        User $actor,
        Pipeline $pipeline,
        array $filters = []
    ): array {
        $query = $this->buildQuery($pipeline, $filters);
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
     * Get a single pipeline stage by ID, scoped to a pipeline.
     */
    public function getById(
        User $user,
        Pipeline $pipeline,
        int $id,
        bool $withTrashed = false
    ): array {
        $pipelineStage = $this->findPipelineStage(
            $pipeline,
            $id,
            $withTrashed
        );

        return array_merge(
            [
                'pipeline_stage' => $this->formatterService->format($pipelineStage),
                'pipeline' => [
                    'id' => $pipeline->id,
                    'title' => $pipeline->title,
                ],
            ],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Build the base query with filters, scoped to a pipeline.
     */
    protected function buildQuery(Pipeline $pipeline, array $filters): Builder
    {
        $query = PipelineStage::query()
            ->where('pipeline_id', $pipeline->id)
            ->with('pipeline', 'creator', 'updater', 'deleter', 'restorer');

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
            'pipeline_stages' => [
                'data' => array_map(
                    fn (PipelineStage $pipelineStage) => $this->formatterService->format($pipelineStage),
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
                'can_create' => $user->can('create', PipelineStage::class),
                'can_view_any' => $user->can('viewAny', PipelineStage::class),
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
     * Find a pipeline stage by ID within a pipeline, with optional trashed records.
     */
    private function findPipelineStage(
        Pipeline $pipeline,
        int $id,
        bool $withTrashed = false
    ): PipelineStage {
        $query = PipelineStage::query()
            ->where('pipeline_id', $pipeline->id)
            ->with('pipeline', 'creator', 'updater', 'deleter', 'restorer');

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
            $filters['sort_by'] ?? 'position',
            $filters['sort_direction'] ?? 'asc'
        );
    }
}

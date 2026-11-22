<?php

declare(strict_types=1);

namespace App\Services\CampaignSegments;

use App\Models\CampaignSegment;
use App\Models\User;
use App\Services\TrashFilterService;
use App\Support\CampaignSegments\CriteriaRules;
use Illuminate\Database\Eloquent\Builder;

class QueryService
{
    /**
     * Relations eager loaded to prevent N+1 queries.
     *
     * @var array<int, string>
     */
    private const RELATIONS = ['creator', 'updater', 'deleter', 'restorer'];

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
     * Get paginated campaign segments with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPaginated(User $user, array $filters = []): array
    {
        $filters = $this->filterService->normalise($filters);

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
     * Get a single campaign segment by ID.
     *
     * @return array<string, mixed>
     */
    public function getById(User $user, CampaignSegment $campaignSegment): array
    {
        $campaignSegment->loadMissing(self::RELATIONS);

        return array_merge(
            ['campaignSegment' => $this->formatterService->format($campaignSegment)],
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
            'fieldOptions' => CriteriaRules::fieldOptions(),
            'operatorOptions' => CriteriaRules::operatorOptions(),
            'maxCriteria' => CriteriaRules::MAX_CRITERIA,
        ];
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<CampaignSegment>
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = CampaignSegment::query()->with(self::RELATIONS);

        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as a plain array.
     *
     * @param  Builder<CampaignSegment>  $query
     * @return array<string, mixed>
     */
    protected function paginate(Builder $query, int $perPage): array
    {
        $paginator = $query->paginate($perPage)->withQueryString();

        /** @var array<int, CampaignSegment> $items */
        $items = $paginator->items();

        return [
            'campaignSegments' => [
                'data' => array_map(
                    fn (CampaignSegment $campaignSegment) => $this->formatterService->format($campaignSegment),
                    $items
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
     * Get permissions for the authenticated user.
     *
     * @return array<string, mixed>
     */
    protected function getPermissions(User $user): array
    {
        return [
            'permissions_meta' => [
                'can_create' => $user->can('create', CampaignSegment::class),
                'can_view_any' => $user->can('viewAny', CampaignSegment::class),
                'can_import' => $user->can('import', CampaignSegment::class),
                'can_export' => $user->can('export', CampaignSegment::class),
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
     * @param  Builder<CampaignSegment>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<CampaignSegment>
     */
    private function applySorting(Builder $query, array $filters): Builder
    {
        $query = $this->trashFilterService->applyFilter(
            $query,
            $filters['trashed'] ?? null
        );

        return $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'created_at',
            $filters['sort_direction'] ?? 'desc'
        );
    }
}
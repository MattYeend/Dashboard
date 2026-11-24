<?php

declare(strict_types=1);

namespace App\Services\CampaignRecipients;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\User;
use App\Services\TrashFilterService;
use Illuminate\Database\Eloquent\Builder;

class QueryService
{
    /**
     * Relations eager loaded to prevent N+1 queries (the repo runs with
     * Model::shouldBeStrict()).
     *
     * @var array<int, string>
     */
    private const RELATIONS = [
        'campaign',
        'contact',
        'creator',
        'updater',
        'deleter',
        'restorer',
    ];

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
     * Get paginated campaign recipients of a campaign with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPaginated(
        User $actor,
        Campaign $campaign,
        array $filters = []
    ): array {
        $query = $this->buildQuery($campaign, $filters);
        $paginated = $this->paginate(
            $query,
            max(1, min((int) ($filters['per_page'] ?? 15), 100))
        );

        return array_merge(
            $paginated,
            ['campaign' => ['id' => $campaign->id, 'name' => $campaign->name]],
            $this->getPermissions($actor),
            $this->baseData(),
        );
    }

    /**
     * Get a single campaign recipient.
     *
     * @return array<string, mixed>
     */
    public function getById(
        User $user,
        Campaign $campaign,
        CampaignRecipient $campaignRecipient
    ): array {
        $campaignRecipient->loadMissing(self::RELATIONS);

        return array_merge(
            ['campaignRecipient' => $this->formatterService->format($campaignRecipient)],
            ['campaign' => ['id' => $campaign->id, 'name' => $campaign->name]],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<CampaignRecipient>
     */
    protected function buildQuery(Campaign $campaign, array $filters): Builder
    {
        $query = CampaignRecipient::query()
            ->where('campaign_id', $campaign->id)
            ->with(self::RELATIONS);

        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as a plain array.
     *
     * @param  Builder<CampaignRecipient>  $query
     * @return array<string, mixed>
     */
    protected function paginate(
        Builder $query,
        int $perPage
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        /** @var array<int, CampaignRecipient> $items */
        $items = $paginator->items();

        return [
            'campaignRecipients' => [
                'data' => array_map(
                    fn (CampaignRecipient $campaignRecipient) => $this->formatterService->format($campaignRecipient),
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
     * Get user permissions for the authenticated user.
     *
     * @return array<string, mixed>
     */
    protected function getPermissions(User $user): array
    {
        return [
            'permissions_meta' => [
                'can_view_any' => $user->can('viewAny', CampaignRecipient::class),
                'can_export' => $user->can('export', CampaignRecipient::class),
            ],
        ];
    }

    /**
     * Get base data for the view, including the status filter options.
     *
     * @return array<string, mixed>
     */
    protected function baseData(): array
    {
        return [
            'sort_fields' => $this->sortingService->getAvailableSortFields(),
            'trash_filters' => $this->trashFilterService->getFilterOptions(),
            'status_options' => array_map(
                fn (string $status): array => [
                    'value' => $status,
                    'label' => ucfirst($status),
                ],
                CampaignRecipient::STATUSES
            ),
        ];
    }

    /**
     * Apply trash filtering and sorting to the query.
     *
     * @param  Builder<CampaignRecipient>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<CampaignRecipient>
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
            $filters['sort_by'] ?? 'created_at',
            $filters['sort_direction'] ?? 'desc'
        );
    }
}
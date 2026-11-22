<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Models\Campaign;
use App\Models\CampaignSegment;
use App\Models\CampaignStatus;
use App\Models\EmailTemplate;
use App\Models\Organisation;
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
        'template',
        'segment',
        'owner',
        'status',
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
     * Get paginated campaigns with filters.
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
     * Get a single campaign by ID.
     *
     * @return array<string, mixed>
     */
    public function getById(User $user, Campaign $campaign): array
    {
        $campaign->loadMissing(self::RELATIONS);

        return array_merge(
            ['campaign' => $this->formatterService->format($campaign)],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get the select options needed to render the create/edit form.
     *
     * Templates and segments are tenant scoped through BelongsToOrganisation.
     * Owners come from the current organisation's active users, so a user
     * from another tenant can never be offered.
     *
     * @return array<string, mixed>
     */
    public function getFormData(): array
    {
        $owners = Organisation::current()?->activeUsers()
            ?? User::query()->whereRaw('1 = 0');

        return [
            'templateOptions' => EmailTemplate::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($row) => ['value' => $row->id, 'label' => $row->name])
                ->all(),
            'segmentOptions' => CampaignSegment::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($row) => ['value' => $row->id, 'label' => $row->name])
                ->all(),
            'ownerOptions' => $owners
                ->orderBy('users.name')
                ->get(['users.id', 'users.name'])
                ->map(fn ($row) => ['value' => $row->id, 'label' => $row->name])
                ->all(),
        ];
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Campaign>
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Campaign::query()->with(self::RELATIONS);
        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as a plain array.
     *
     * @param  Builder<Campaign>  $query
     * @return array<string, mixed>
     */
    protected function paginate(
        Builder $query,
        int $perPage
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        /** @var array<int, Campaign> $items */
        $items = $paginator->items();

        return [
            'campaigns' => [
                'data' => array_map(
                    fn (Campaign $campaign) => $this->formatterService->format($campaign),
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
                'can_create' => $user->can('create', Campaign::class),
                'can_view_any' => $user->can('viewAny', Campaign::class),
                'can_export' => $user->can('export', Campaign::class),
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
            'status_options' => CampaignStatus::query()
                ->orderBy('title')
                ->get(['id', 'title'])
                ->map(fn ($row) => ['value' => $row->id, 'label' => $row->title])
                ->all(),
        ];
    }

    /**
     * Apply trash filtering and sorting to the query.
     *
     * @param  Builder<Campaign>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Campaign>
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

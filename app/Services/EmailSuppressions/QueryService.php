<?php

declare(strict_types=1);

namespace App\Services\EmailSuppressions;

use App\Models\EmailSuppression;
use App\Models\User;
use App\Services\TrashFilterService;
use Illuminate\Database\Eloquent\Builder;

class QueryService
{
    /**
     * Relations eager loaded to prevent N+1 queries (the repo runs with
     * Model::shouldBeStrict()). campaign and contact are read by the
     * FormatterService, so they must be loaded here.
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
     * Get paginated email suppressions with filters.
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
     * Get a single email suppression by ID.
     *
     * @return array<string, mixed>
     */
    public function getById(User $user, EmailSuppression $emailSuppression): array
    {
        $emailSuppression->loadMissing(self::RELATIONS);

        return array_merge(
            ['emailSuppression' => $this->formatterService->format($emailSuppression)],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get the select options needed to render the create/edit form.
     *
     * @return array<string, mixed>
     */
    public function getFormData(): array
    {
        return [
            'reasonOptions' => $this->reasonOptions(),
        ];
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<EmailSuppression>
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = EmailSuppression::query()->with(self::RELATIONS);
        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as a plain array.
     *
     * @param  Builder<EmailSuppression>  $query
     * @return array<string, mixed>
     */
    protected function paginate(
        Builder $query,
        int $perPage
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        /** @var array<int, EmailSuppression> $items */
        $items = $paginator->items();

        return [
            'emailSuppressions' => [
                'data' => array_map(
                    fn (EmailSuppression $emailSuppression) => $this->formatterService->format($emailSuppression),
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
                'can_create' => $user->can('create', EmailSuppression::class),
                'can_view_any' => $user->can('viewAny', EmailSuppression::class),
                'can_import' => $user->can('import', EmailSuppression::class),
                'can_export' => $user->can('export', EmailSuppression::class),
            ],
        ];
    }

    /**
     * Get base data for the view, including the reason filter options.
     *
     * @return array<string, mixed>
     */
    protected function baseData(): array
    {
        return [
            'sort_fields' => $this->sortingService->getAvailableSortFields(),
            'trash_filters' => $this->trashFilterService->getFilterOptions(),
            'reason_options' => $this->reasonOptions(),
        ];
    }

    /**
     * The allow-listed suppression reasons as select options.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function reasonOptions(): array
    {
        return array_map(
            fn (string $reason): array => [
                'value' => $reason,
                'label' => ucfirst($reason),
            ],
            EmailSuppression::REASONS
        );
    }

    /**
     * Apply trash filtering and sorting to the query.
     *
     * @param  Builder<EmailSuppression>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<EmailSuppression>
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
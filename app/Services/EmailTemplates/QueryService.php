<?php

declare(strict_types=1);

namespace App\Services\EmailTemplates;

use App\Models\EmailTemplate;
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
     * Get paginated email templates with filters.
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
     * Get a single email template by ID.
     *
     * @return array<string, mixed>
     */
    public function getById(User $user, EmailTemplate $emailTemplate): array
    {
        $emailTemplate->loadMissing(self::RELATIONS);

        return array_merge(
            ['emailTemplate' => $this->formatterService->format($emailTemplate)],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get the data needed to render the create/edit form.
     *
     * @return array<string, mixed>
     */
    public function getFormData(): array
    {
        return [
            // no select options for this model
        ];
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<EmailTemplate>
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = EmailTemplate::query()->with(self::RELATIONS);
        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as a plain array.
     *
     * @param  Builder<EmailTemplate>  $query
     * @return array<string, mixed>
     */
    protected function paginate(
        Builder $query,
        int $perPage
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        /** @var array<int, EmailTemplate> $items */
        $items = $paginator->items();

        return [
            'emailTemplates' => [
                'data' => array_map(
                    fn (EmailTemplate $emailTemplate) => $this->formatterService->format($emailTemplate),
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
                'can_create' => $user->can('create', EmailTemplate::class),
                'can_view_any' => $user->can('viewAny', EmailTemplate::class),
                'can_export' => $user->can('export', EmailTemplate::class),
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
     * @param  Builder<EmailTemplate>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<EmailTemplate>
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

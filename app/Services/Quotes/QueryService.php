<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Models\Quote;
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
        protected readonly QuotableTypeRegistryService $registry,
    ) {}

    /**
     * Get paginated quotes with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPaginated(User $actor, array $filters = []): array
    {
        $query = $this->buildQuery($filters);
        $paginated = $this->paginate(
            $query,
            max(1, min((int) ($filters['per_page'] ?? 15), 100))
        );

        return array_merge(
            $paginated,
            $this->getPermissions($actor),
            $this->baseData(),
            ['statuses' => $this->statuses()],
        );
    }

    /**
     * Get a single quote by ID, with the abilities the page needs.
     *
     * @return array<string, mixed>
     */
    public function getById(User $user, Quote $quote): array
    {
        $quote->loadMissing([
            'quotable',
            'status',
            'convertedOrder',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        return array_merge(
            ['quote' => $this->formatterService->format($quote)],
            ['abilities' => [
                'update' => $user->can('update', $quote),
                'delete' => $user->can('delete', $quote),
                'send' => $user->can('send', $quote),
                'accept' => $user->can('accept', $quote),
                'decline' => $user->can('decline', $quote),
                'convert' => $user->can('convert', $quote),
            ]],
            $this->getFormData(),
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get the data needed to render the Create/Edit quote form.
     *
     * @return array<string, mixed>
     */
    public function getFormData(): array
    {
        return [
            'quotableTypes' => $this->registry->types(),
            'currencies' => config('currencies.allowed'),
        ];
    }

    /**
     * Get the customer options for a type, for the dependent dropdown.
     *
     * @return array<int, array{value: int, label: string}>
     */
    public function getQuotableOptions(string $type): array
    {
        return $this->registry->optionsFor($type);
    }

    /**
     * Statuses for the index filter.
     *
     * @return \Illuminate\Support\Collection<int, QuoteStatus>
     */
    protected function statuses()
    {
        return QuoteStatus::orderBy('title')->get(['id', 'title', 'background_colour', 'text_colour']);
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Quote>
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Quote::query()->with([
            'quotable',
            'status',
            'convertedOrder',
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
     * @param  Builder<Quote>  $query
     * @return array<string, mixed>
     */
    protected function paginate(Builder $query, int $perPage): array
    {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'quotes' => [
                'data' => array_map(
                    fn (Quote $quote) => $this->formatterService->format($quote),
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
                'can_create' => $user->can('create', Quote::class),
                'can_view_any' => $user->can('viewAny', Quote::class),
                'can_export' => $user->can('export', Quote::class),
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
     * @param  Builder<Quote>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Quote>
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
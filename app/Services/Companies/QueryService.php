<?php

namespace App\Services\Companies;

use App\Models\Company;
use App\Models\Industry;
use App\Models\Tag;
use App\Models\User;
use App\Services\TrashFilterService;
use App\Services\Contacts\FormatterService as ContactFormatterService;
use App\Services\Deals\FormatterService as DealFormatterService;
use App\Services\Orders\FormatterService as OrderFormatterService;
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
        protected readonly ContactFormatterService $contactFormatterService,
        protected readonly OrderFormatterService $orderFormatterService,
        protected readonly DealFormatterService $dealFormatterService
    ) {}

    /**
     * Get paginated companies with filters.
     */
    public function getPaginated(
        User $user,
        array $filters = []
    ): array {
        $query = $this->buildQuery($filters);
        $paginated = $this->paginate(
            $query,
            min((int) ($filters['per_page'] ?? 15), 100)
        );

        return array_merge(
            $paginated,
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get a single company by ID.
     */
    public function getById(
        User $user,
        int $id,
        bool $withTrashed = false
    ): array {
        $company = $this->findCompany($id, $withTrashed);

        return array_merge(
            ['company' => $this->formatterService->format($company)],
            $this->getFormData(),
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get data needed to populate create and edit forms.
     */
    public function getFormData(): array
    {
        return [
            'industries' => Industry::orderBy('title')->get(['id', 'title']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ];
    }

        /**
     * Get a capped, recent slice of related contacts, orders and deals for the Show page widget.
     */
    public function getRelatedSummaries(Company $company): array
    {
        $company->loadMissing([
            'contacts' => fn (Builder $query) => $query->latest()->limit(5),
            'orders' => fn (Builder $query) => $query->with('status')->latest('ordered_at')->limit(5),
            'deals' => fn (Builder $query) => $query->with(['stage', 'status'])->latest()->limit(5),
        ]);

        return [
            'related_contacts' => $company->contacts
                ->map(fn ($contact) => $this->contactFormatterService->format($contact))
                ->all(),
            'related_orders' => $company->orders
                ->map(fn ($order) => $this->orderFormatterService->format($order))
                ->all(),
            'related_deals' => $company->deals
                ->map(fn ($deal) => $this->dealFormatterService->format($deal))
                ->all(),
        ];
    }

    /**
     * Build the base query with filters.
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Company::query()->with([
            'creator',
            'updater',
            'deleter',
            'restorer',
            'industry',
            'accountManager',
            'tags',
        ]);
        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as a plain array.
     */
    protected function paginate(
        Builder $query,
        int $perPage
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'companies' => [
                'data' => array_map(
                    fn (Company $company) => $this->formatterService->format($company),
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
                'can_create' => $user->can('create', Company::class),
                'can_view_any' => $user->can('viewAny', Company::class),
                'can_export' => $user->can('export', Company::class),
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
     * Find a company by ID with optional trashed records.
     */
    private function findCompany(
        int $id,
        bool $withTrashed = false
    ): Company {
        $query = Company::query()->with([
            'creator',
            'updater',
            'deleter',
            'restorer',
            'industry',
            'accountManager',
            'tags',
        ]);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    /**
     * Apply sorting and trash filtering to the query.
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

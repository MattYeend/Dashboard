<?php

namespace App\Services\Invoices;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
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
     * Get paginated invoices with filters.
     */
    public function getPaginated(
        User $user,
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
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get a single invoice by ID.
     */
    public function getById(
        User $user,
        int $id,
        bool $withTrashed = false
    ): array {
        $invoice = $this->findInvoice(
            $id,
            $withTrashed
        );

        return array_merge(
            ['invoice' => $this->formatterService->format($invoice)],
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
            'statuses' => InvoiceStatus::orderBy('title')->get([
                'id',
                'title',
                'background_colour',
                'text_colour',
            ]),
            'companies' => Company::orderBy('name')->get([
                'id',
                'name',
            ]),
        ];
    }

    /**
     * Build the base query with filters.
     */
    protected function buildQuery(
        array $filters
    ): Builder {
        $query = Invoice::query()->with([
            'company',
            'contact',
            'order',
            'status',
        ]);
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
     * Paginate the query and return as a plain array.
     */
    protected function paginate(
        Builder $query,
        int $perPage
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'invoices' => [
                'data' => array_map(
                    fn (Invoice $invoice) => $this->formatterService->format(
                        $invoice
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
    protected function getPermissions(
        User $user
    ): array {
        if (! $user) {
            return ['permissions_meta' => []];
        }

        return [
            'permissions_meta' => [
                'can_create' => $user->can(
                    'create',
                    Invoice::class
                ),
                'can_view_any' => $user->can(
                    'viewAny',
                    Invoice::class
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
     * Find an invoice by ID with optional trashed records.
     */
    private function findInvoice(
        int $id,
        bool $withTrashed = false
    ): Invoice {
        $query = Invoice::query()->with([
            'company',
            'contact',
            'order',
            'status',
        ]);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail(
            $id
        );
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
            $filters['sort_by'] ?? 'due_date',
            $filters['sort_direction'] ?? 'asc'
        );
    }
}

<?php

namespace App\Services\InvoiceItems;

use App\Models\Invoice;
use App\Models\InvoiceItem;
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
     * Get paginated invoice items for a given invoice, with filters.
     */
    public function getPaginated(
        User $user,
        Invoice $invoice,
        array $filters = []
    ): array {
        $query = $this->buildQuery(
            $invoice,
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
     * Get a single invoice item by ID, scoped to its parent invoice.
     */
    public function getById(
        User $user,
        Invoice $invoice,
        int $id,
        bool $withTrashed = false
    ): array {
        $invoiceItem = $this->findInvoiceItem(
            $invoice,
            $id,
            $withTrashed
        );

        return array_merge(
            ['invoice_item' => $this->formatterService->format($invoiceItem)],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get data needed to populate create and edit forms.
     */
    public function getFormData(Invoice $invoice): array
    {
        return [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ],
        ];
    }

    /**
     * Build the base query with filters, scoped to the parent invoice.
     */
    protected function buildQuery(
        Invoice $invoice,
        array $filters
    ): Builder {
        $query = $invoice->items()->getQuery();
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
            'invoice_items' => [
                'data' => array_map(
                    fn (InvoiceItem $invoiceItem) => $this->formatterService->format(
                        $invoiceItem
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
                    InvoiceItem::class
                ),
                'can_view_any' => $user->can(
                    'viewAny',
                    InvoiceItem::class
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
     * Find an invoice item by ID, scoped to its parent invoice.
     */
    private function findInvoiceItem(
        Invoice $invoice,
        int $id,
        bool $withTrashed = false
    ): InvoiceItem {
        $query = $invoice->items()->getQuery();

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
            $filters['sort_by'] ?? 'position',
            $filters['sort_direction'] ?? 'asc'
        );
    }
}

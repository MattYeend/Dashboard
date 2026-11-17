<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payments\ActiveCheckerService as PaymentActiveCheckerService;
use App\Services\Payments\QueryService as PaymentQueryService;
use App\Services\TrashFilterService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
        protected readonly PaymentQueryService $paymentQuery,
        protected readonly PaymentActiveCheckerService $paymentChecker
    ) {}

    /**
     * Get paginated invoices with filters.
     */
    public function getPaginated(
        User $user,
        array $filters = []
    ): array {
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
     * Get a single invoice by ID.
     */
    public function getById(User $user, Invoice $invoice): array
    {
        $invoice->loadMissing([
            'company',
            'contact.addresses',
            'order',
            'status',
            'items' => fn ($query) => $query->orderBy('position'),
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        return array_merge(
            [
                'invoice' => $this->formatterService->format($invoice),
                'payments' => $user->can('viewAny', Payment::class)
                    ? $this->paymentQuery->forInvoice($user, $invoice)
                    : [],
            ],
            $this->getPermissions($user, $invoice),
            $this->baseData(),
        );
    }

    /**
     * Get data needed to populate create and edit forms.
     */
    public function getFormData(?Invoice $invoice = null): array
    {
        return [
            'statuses' => InvoiceStatus::query()
                ->where(function (Builder $query) use ($invoice): void {
                    $query->whereNotIn('title', ['Partially Paid', 'Paid']);

                    if ($invoice !== null && $invoice->status_id !== null) {
                        $query->orWhere('id', $invoice->status_id);
                    }
                })
                ->orderBy('title')
                ->get([
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
    protected function buildQuery(array $filters): Builder
    {
        $query = Invoice::query()->withCount([
            'payments' => static function (Builder $query): void {
                $query->withoutGlobalScope(SoftDeletingScope::class);
            },
        ])->with([
            'company',
            'contact.addresses',
            'order',
            'status',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as a plain array.
     *
     * @param  Builder<Invoice>  $query
     */
    protected function paginate(
        Builder $query,
        int $perPage
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'invoices' => [
                'data' => array_map(
                    fn (Invoice $invoice) => $this->formatterService->format($invoice),
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
    protected function getPermissions(User $user, ?Invoice $invoice = null): array
    {
        $permissions = [
            'can_create' => $user->can('create', Invoice::class),
            'can_view_any' => $user->can('viewAny', Invoice::class),
            'can_export' => $user->can('export', Invoice::class),
        ];

        if ($invoice !== null) {
            $permissions['can_record_payment'] = $user->can('create', Payment::class)
                && $this->paymentChecker->invoiceAcceptsPayments($invoice);
        }

        return ['permissions_meta' => $permissions];
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

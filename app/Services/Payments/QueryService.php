<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentStatus;
use App\Models\User;
use App\Services\Invoices\BalanceService;
use App\Services\TrashFilterService;
use Illuminate\Database\Eloquent\Builder;

class QueryService
{
    /**
     * The most invoices offered in the payment form dropdown.
     */
    protected const MAX_INVOICE_OPTIONS = 500;

    /**
     * Inject the required services into the query service.
     */
    public function __construct(
        protected readonly SortingService $sortingService,
        protected readonly TrashFilterService $trashFilterService,
        protected readonly FilterService $filterService,
        protected readonly FormatterService $formatterService,
        protected readonly BalanceService $balance,
    ) {}

    /**
     * Get paginated payments with filters.
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
            $actor,
            max(1, min((int) ($filters['per_page'] ?? 15), 100))
        );

        return array_merge(
            $paginated,
            $this->getPermissions($actor),
            $this->baseData(),
        );
    }

    /**
     * Get a single payment by ID, with the refunds raised against it.
     *
     * @return array<string, mixed>
     */
    public function getById(User $user, Payment $payment): array
    {
        $payment->loadMissing(['refunds.status', 'refunds.creator']);

        return array_merge(
            [
                'payment' => $this->formatterService->format($payment, $user),
                'refunds' => $payment->refunds
                    ->map(fn (Payment $refund): array => $this->formatterService->format($refund, $user))
                    ->values()
                    ->all(),
            ],
            $this->getFormData(),
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get the data needed to render the Create/Edit payment form.
     *
     * @return array<string, mixed>
     */
    public function getFormData(?int $invoiceId = null): array
    {
        $invoices = $this->payableInvoiceOptions();

        return [
            'invoice' => $invoiceId === null
                ? null
                : ($invoices->firstWhere('id', $invoiceId)),
            'invoices' => $invoices->values()->all(),
            'methods' => PaymentMethod::options(),
        ];
    }

    /**
     * Get the payments and refunds recorded against one invoice, for the
     * Invoice Show page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forInvoice(User $actor, Invoice $invoice): array
    {
        return Payment::query()
            ->with(['status', 'invoice'])
            ->where('invoice_id', $invoice->id)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Payment $payment): array => $this->formatterService->format($payment, $actor))
            ->all();
    }

    /**
     * Invoices that can currently receive a payment, with their balance.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function payableInvoiceOptions(): \Illuminate\Support\Collection
    {
        return Invoice::query()
            ->with('status')
            ->whereHas('status', fn (Builder $status) => $status->whereIn('title', BalanceService::PAYABLE_STATUSES))
            ->orderBy('invoice_number')
            ->limit(self::MAX_INVOICE_OPTIONS)
            ->get()
            ->map(fn (Invoice $invoice): array => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total_amount' => $this->balance->poundsFromPence($invoice->total),
                'amount_paid' => $this->balance->poundsFromPence($invoice->amount_paid),
                'balance_due' => $this->balance->balanceDue($invoice),
            ]);
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Payment>
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Payment::query()->with([
            'status',
            'invoice',
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
     * @param  Builder<Payment>  $query
     * @return array<string, mixed>
     */
    protected function paginate(
        Builder $query,
        User $actor,
        int $perPage
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'payments' => [
                'data' => array_map(
                    fn (Payment $payment) => $this->formatterService->format($payment, $actor),
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
                'can_create' => $user->can('create', Payment::class),
                'can_view_any' => $user->can('viewAny', Payment::class),
                'can_export' => $user->can('export', Payment::class),
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
            'status_options' => PaymentStatus::query()
                ->orderBy('title')
                ->get(['id', 'title'])
                ->map(fn (PaymentStatus $status): array => ['id' => $status->id, 'title' => $status->title])
                ->all(),
        ];
    }

    /**
     * Apply trash filtering and sorting to the query.
     *
     * @param  Builder<Payment>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Payment>
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
            $filters['sort_by'] ?? 'paid_at',
            $filters['sort_direction'] ?? 'desc'
        );
    }
}

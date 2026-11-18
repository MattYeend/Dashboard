<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
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
    ) {}

    /**
     * Get paginated quote items for a given quote, with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPaginated(
        User $user,
        Quote $quote,
        array $filters = []
    ): array {
        $paginated = $this->paginate(
            $this->buildQuery($quote, $filters),
            max(1, min((int) ($filters['per_page'] ?? 15), 100))
        );

        return array_merge(
            $paginated,
            $this->getPermissions($user, $quote),
            $this->baseData(),
            $this->getFormData($quote),
        );
    }

    /**
     * Get a single quote item, scoped to its parent quote.
     *
     * @return array<string, mixed>
     */
    public function getById(
        User $user,
        Quote $quote,
        QuoteItem $quoteItem
    ): array {
        $quoteItem->loadMissing([
            'product',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        return array_merge(
            ['item' => $this->formatterService->format($quoteItem)],
            $this->getPermissions($user, $quote),
            $this->baseData(),
            $this->getFormData($quote),
        );
    }

    /**
     * Get data needed to populate create and edit forms.
     *
     * @return array<string, mixed>
     */
    public function getFormData(Quote $quote): array
    {
        return [
            'quote' => [
                'id' => $quote->id,
                'quote_number' => $quote->quote_number,
                'currency' => $quote->currency,
            ],
            'products' => $this->productOptions($quote),
        ];
    }

    /**
     * Get the formatted lines of a quote.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function forQuote(Quote $quote, array $filters = []): array
    {
        return $this->buildQuery($quote, $filters)
            ->get()
            ->map(fn (QuoteItem $item) => $this->formatterService->format($item))
            ->all();
    }

    /**
     * Products that can be chosen on a line (active, current tenant).
     *
     * @return array<int, array<string, mixed>>
     */
    public function productOptions(Quote $quote): array
    {
        $columns = ['id', 'sku', 'name', 'description', 'unit', 'unit_price', 'tax_rate'];

        return Product::query()
            ->where('is_active', true)
            ->where('currency', $quote->currency)
            ->orderBy('name')
            ->get($columns)
            ->map(static fn (Product $product): array => $product->only($columns))
            ->values()
            ->all();
    }

    /**
     * Build the base query with filters, scoped to the parent quote.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<QuoteItem>
     */
    protected function buildQuery(Quote $quote, array $filters): Builder
    {
        $query = $quote->items()->with([
            'product',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ])
            ->getQuery();

        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as a plain array.
     *
     * @param  Builder<QuoteItem>  $query
     * @return array<string, mixed>
     */
    protected function paginate(Builder $query, int $perPage): array
    {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'quote_items' => [
                'data' => array_map(
                    fn (QuoteItem $quoteItem) => $this->formatterService->format($quoteItem),
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
    protected function getPermissions(User $user, Quote $quote): array
    {
        return [
            'permissions_meta' => [
                'can_create' => $user->can('create', QuoteItem::class),
                'can_view_any' => $user->can('viewAny', QuoteItem::class),
                'can_export' => $user->can('export', [QuoteItem::class, $quote]),
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
     * @param  Builder<QuoteItem>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<QuoteItem>
     */
    private function applySorting(Builder $query, array $filters): Builder
    {
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

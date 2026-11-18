<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Services\TrashFilterService;

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
     * Get the formatted lines of a quote.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function forQuote(Quote $quote, array $filters = []): array
    {
        $query = QuoteItem::query()->with(['product', 'creator', 'updater']);

        $query = $this->filterService->forQuote($query, $quote->id);
        $query = $this->filterService->applyAll($query, $filters);
        $query = $this->trashFilterService->applyFilter($query, $filters['trashed'] ?? null);
        $query = $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'position',
            $filters['sort_direction'] ?? 'asc'
        );

        return $query
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
        return Product::query()
            ->where('is_active', true)
            ->where('currency', $quote->currency)
            ->orderBy('name')
            ->get(['id', 'sku', 'name', 'description', 'unit', 'unit_price', 'tax_rate'])
            ->all();
    }
}
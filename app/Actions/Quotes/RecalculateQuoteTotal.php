<?php

declare(strict_types=1);

namespace App\Actions\Quote;

use App\Models\Quote;
use Illuminate\Support\Facades\DB;

class RecalculateQuoteTotal
{
    public function __construct(
        protected readonly CalculateQuoteTotals $calculateQuoteTotals,
    ) {}

    /**
     * Recalculate and persist the quote subtotal, tax total, and total
     * from its line items.
     *
     * The quote row is locked first so concurrent line edits cannot
     * overwrite each other with stale totals.
     */
    public function execute(Quote $quote): Quote
    {
        return DB::transaction(function () use ($quote): Quote {
            Quote::query()->whereKey($quote->id)->lockForUpdate()->firstOrFail();

            $totals = $this->calculateQuoteTotals->execute($quote);

            $quote->subtotal = $totals['subtotal'];
            $quote->tax_total = $totals['tax_total'];
            $quote->total = $totals['total'];
            $quote->save();

            return $quote;
        });
    }
}
<?php

declare(strict_types=1);

namespace App\Actions\Quotes;

use App\Models\Quote;

class CalculateQuoteTotals
{
    /**
     * Calculate the subtotal, tax total, and total for a quote from its
     * live line items, without persisting anything.
     *
     * Quote lines are DECIMAL(10,2), and SUM() on DECIMAL is exact in
     * MySQL and PostgreSQL, so values are returned as DECIMAL strings.
     *
     * Any default ordering on the items relationship is cleared, because
     * PostgreSQL rejects a non-aggregated ORDER BY column in an aggregate
     * query.
     *
     * @return array{subtotal: string, tax_total: string, total: string}
     */
    public function execute(Quote $quote): array
    {
        $aggregates = $quote->items()
            ->toBase()
            ->reorder()
            ->selectRaw('COALESCE(SUM(line_total), 0) as subtotal')
            ->selectRaw('COALESCE(SUM(line_tax), 0) as tax_total')
            ->selectRaw('COALESCE(SUM(line_total + line_tax), 0) as total')
            ->first();

        return [
            'subtotal' => (string) ($aggregates->subtotal ?? '0.00'),
            'tax_total' => (string) ($aggregates->tax_total ?? '0.00'),
            'total' => (string) ($aggregates->total ?? '0.00'),
        ];
    }
}

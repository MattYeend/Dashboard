<?php

namespace App\Actions\Invoice;

use App\Models\Invoice;

class CalculateInvoiceTotals
{
    /**
     * Calculate the subtotal, tax total, and total for an invoice from its
     * line items, without persisting anything.
     *
     * @return array{subtotal: int, tax_total: int, total: int}
     */
    public function execute(Invoice $invoice): array
    {
        $aggregates = $invoice->items()
            ->selectRaw('COALESCE(SUM(quantity * unit_price), 0) as subtotal')
            ->selectRaw('COALESCE(SUM(total), 0) as total')
            ->first();

        $subtotal = (int) $aggregates->subtotal;
        $total = (int) $aggregates->total;

        return [
            'subtotal' => $subtotal,
            'tax_total' => $total - $subtotal,
            'total' => $total,
        ];
    }
}

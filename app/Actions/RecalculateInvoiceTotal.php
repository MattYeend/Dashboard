<?php

namespace App\Actions;

use App\Models\Invoice;

class RecalculateInvoiceTotal
{
    /**
     * Recalculate and persist the invoice subtotal, tax total, and total
     * from its line items.
     */
    public function execute(Invoice $invoice): Invoice
    {
        $aggregates = $invoice->items()
            ->selectRaw('COALESCE(SUM(quantity * unit_price), 0) as subtotal')
            ->selectRaw('COALESCE(SUM(total), 0) as total')
            ->first();

        $subtotal = (int) $aggregates->subtotal;
        $total = (int) $aggregates->total;

        $invoice->subtotal = $subtotal;
        $invoice->tax_total = $total - $subtotal;
        $invoice->total = $total;
        $invoice->save();

        return $invoice;
    }
}

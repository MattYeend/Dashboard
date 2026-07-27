<?php

namespace App\Actions\Invoice;

use App\Models\Invoice;

class RecalculateInvoiceTotal
{
    public function __construct(
        protected readonly CalculateInvoiceTotals $calculateInvoiceTotals,
    ) {}

    /**
     * Recalculate and persist the invoice subtotal, tax total, and total
     * from its line items.
     */
    public function execute(Invoice $invoice): Invoice
    {
        $totals = $this->calculateInvoiceTotals->execute($invoice);

        $invoice->subtotal = $totals['subtotal'];
        $invoice->tax_total = $totals['tax_total'];
        $invoice->total = $totals['total'];
        $invoice->save();

        return $invoice;
    }
}

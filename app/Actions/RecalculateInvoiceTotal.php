<?php

namespace App\Actions;

use App\Models\Invoice;

class RecalculateInvoiceTotal
{
    /**
     * Recalculate and persist the invoice total from its line items.
     */
    public function execute(Invoice $invoice): Invoice
    {
        $invoice->total = $invoice->items()->sum('line_total');
        $invoice->save();

        return $invoice;
    }
}

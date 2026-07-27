<?php

namespace App\Actions\Invoice;

use App\Models\Invoice;
use App\Models\InvoiceStatus;

class MarkInvoiceAsPaid
{
    /**
     * Set an invoice's status to Paid and record the payment timestamp.
     */
    public function execute(Invoice $invoice, int $actorId): Invoice
    {
        $status = InvoiceStatus::where('title', 'Paid')->first();

        $invoice->update([
            'status_id' => $status?->id,
            'paid_at' => now(),
            'updated_by' => $actorId,
        ]);

        return $invoice->fresh();
    }
}

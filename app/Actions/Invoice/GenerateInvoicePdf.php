<?php

namespace App\Actions\Invoice;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

class GenerateInvoicePdf
{
    public function __construct(
        protected readonly CalculateInvoiceTotals $calculateInvoiceTotals,
    ) {}

    /**
     * Build a PDF representation of an invoice.
     */
    public function execute(Invoice $invoice): DomPdf
    {
        $invoice->loadMissing(['items', 'contact', 'status']);

        $totals = $this->calculateInvoiceTotals->execute($invoice);

        return Pdf::loadView('pdfs.invoice', [
            'invoice' => $invoice,
            'totals' => $totals,
        ]);
    }
}

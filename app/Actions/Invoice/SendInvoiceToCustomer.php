<?php

namespace App\Actions\Invoice;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class SendInvoiceToCustomer
{
    public function __construct(
        protected readonly GenerateInvoicePdf $generateInvoicePdf,
    ) {}

    /**
     * Email the invoice PDF to its associated contact.
     *
     * @throws RuntimeException
     */
    public function execute(Invoice $invoice): void
    {
        $invoice->loadMissing('contact');

        $recipient = $invoice->contact?->email;

        if ($recipient === null) {
            throw new RuntimeException('Invoice has no contact email address to send to.');
        }

        $pdf = $this->generateInvoicePdf->execute($invoice);

        Mail::to($recipient)->send(
            new InvoiceMail($invoice, $pdf->output())
        );
    }
}

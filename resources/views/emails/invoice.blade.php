<x-mail::message>
# Invoice {{ $invoice->invoice_number ?? '#' . $invoice->id }}

Hello,

Please find attached your invoice from {{ config('app.name') }}.

<x-mail::button :url="route('invoices.pdf', $invoice)">
View Invoice
</x-mail::button>

If you have any questions about this invoice, please get in touch.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
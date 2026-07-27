<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number ?? $invoice->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1a1a1a; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { border-bottom: 2px solid #333; }
        .totals td { border: none; }
        .totals tr td:first-child { text-align: right; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Invoice {{ $invoice->invoice_number ?? '#' . $invoice->id }}</h1>

    @if($invoice->issue_date)
        <p>Issued: {{ \Illuminate\Support\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</p>
    @endif

    @if($invoice->due_date)
        <p>Due: {{ \Illuminate\Support\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</p>
    @endif

    @if($invoice->contact)
        <h3>Bill to</h3>
        <p>
            {{ $invoice->contact->email }}<br>
            @if($invoice->contact->address){{ $invoice->contact->address }}<br>@endif
            @if($invoice->contact->city){{ $invoice->contact->city }}<br>@endif
            @if($invoice->contact->postal_code){{ $invoice->contact->postal_code }}<br>@endif
            @if($invoice->contact->country){{ $invoice->contact->country }}@endif
        </p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Unit price</th>
                <th class="text-right">Tax rate</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price / 100, 2) }}</td>
                    <td class="text-right">{{ $item->tax_rate }}%</td>
                    <td class="text-right">{{ number_format($item->total / 100, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="text-right">{{ number_format($totals['subtotal'] / 100, 2) }}</td>
        </tr>
        <tr>
            <td>Tax</td>
            <td class="text-right">{{ number_format($totals['tax_total'] / 100, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td class="text-right"><strong>{{ number_format($totals['total'] / 100, 2) }}</strong></td>
        </tr>
    </table>
</body>
</html>
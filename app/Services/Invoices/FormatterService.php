<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Services\CurrencyFormatterService;

class FormatterService
{
    public function __construct(
        private readonly CurrencyFormatterService $currencyFormatter
    ) {}

    /**
     * Format a single invoice with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Invoice $invoice): array
    {
        $contact = $invoice->contact;
        $address = $contact?->addresses
            ->firstWhere('is_primary', true)
            ?? $contact?->addresses->first();

        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'company_id' => $invoice->company_id,
            'order_id' => $invoice->order_id,
            'status_id' => $invoice->status_id,
            'issue_date' => $invoice->issue_date?->format('Y-m-d'),
            'due_date' => $invoice->due_date?->format('Y-m-d'),
            'sent_at' => $invoice->sent_at?->format('Y-m-d'),
            'paid_at' => $invoice->paid_at?->format('Y-m-d'),
            'subtotal' => $invoice->subtotal,
            'tax_total' => $invoice->tax_total,
            'total' => $invoice->total,
            'items' => $invoice->relationLoaded('items')
                ? $invoice->items->map(fn ($item) => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_rate' => $item->tax_rate,
                    'total' => $item->total,
                    'position' => $item->position,
                ])->values()->all()
                : null,
            'items_count' => $invoice->items_count
                ?? ($invoice->relationLoaded('items')
                    ? $invoice->items->count()
                    : null),
            'currency' => $invoice->currency,
            'formatted_total' => $this->currencyFormatter->format(
                (float) $invoice->total,
                $invoice->currency,
            ),
            'notes' => $invoice->notes,
            'meta' => $invoice->meta,
            'created_at' => $invoice->created_at,
            'updated_at' => $invoice->updated_at,
            'deleted_at' => $invoice->deleted_at,
            'restored_at' => $invoice->restored_at,
            'creator' => $invoice->creator ? ['id' => $invoice->creator->id, 'name' => $invoice->creator->name] : null,
            'updater' => $invoice->updater ? ['id' => $invoice->updater->id, 'name' => $invoice->updater->name] : null,
            'deleter' => $invoice->deleter ? ['id' => $invoice->deleter->id, 'name' => $invoice->deleter->name] : null,
            'restorer' => $invoice->restorer ? ['id' => $invoice->restorer->id, 'name' => $invoice->restorer->name] : null,
            'company' => $invoice->company ? [
                'id' => $invoice->company->id,
                'name' => $invoice->company->name,
            ] : null,
            'contact' => $contact ? [
                'id' => $contact->id,
                'name' => $contact->name,
                'phone' => $contact->phone,
                'email' => $contact->email,
                'address' => $address?->address_line_one,
                'address_line_two' => $address?->address_line_two,
                'town' => $address?->town,
                'city' => $address?->city,
                'county' => $address?->county,
                'postcode' => $address?->postcode,
                'country' => $address?->country,
            ] : null,
            'order' => $invoice->order ? [
                'id' => $invoice->order->id,
            ] : null,
            'status' => $invoice->status ? [
                'id' => $invoice->status->id,
                'title' => $invoice->status->title,
                'background_colour' => $invoice->status->background_colour,
                'text_colour' => $invoice->status->text_colour,
            ] : null,
        ];
    }
}

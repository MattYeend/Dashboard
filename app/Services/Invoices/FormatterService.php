<?php

namespace App\Services\Invoices;

use App\Models\Invoice;

class FormatterService
{
    /**
     * Format a single invoice with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Invoice $invoice): array
    {
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
            'currency' => $invoice->currency,
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
            'contact' => $invoice->contact ? [
                'id' => $invoice->contact->id,
                'phone' => $invoice->contact->phone,
                'email' => $invoice->contact->email,
                'address' => $invoice->contact->address,
                'city' => $invoice->contact->city,
                'postal_code' => $invoice->contact->postal_code,
                'country' => $invoice->contact->country,
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

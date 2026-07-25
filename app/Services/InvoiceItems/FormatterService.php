<?php

namespace App\Services\InvoiceItems;

use App\Models\InvoiceItem;

class FormatterService
{
    /**
     * Format a single invoice item with all data.
     *
     * @return array<string, mixed>
     */
    public function format(InvoiceItem $invoiceItem): array
    {
        return [
            'id' => $invoiceItem->id,
            'invoice_id' => $invoiceItem->invoice_id,
            'description' => $invoiceItem->description,
            'quantity' => $invoiceItem->quantity,
            'unit_price' => $invoiceItem->unit_price,
            'tax_rate' => $invoiceItem->tax_rate,
            'total' => $invoiceItem->total,
            'position' => $invoiceItem->position,
            'meta' => $invoiceItem->meta,
            'created_at' => $invoiceItem->created_at,
            'updated_at' => $invoiceItem->updated_at,
            'deleted_at' => $invoiceItem->deleted_at,
            'restored_at' => $invoiceItem->restored_at,
            'creator' => $invoiceItem->creator ? ['id' => $invoiceItem->creator->id, 'name' => $invoiceItem->creator->name] : null,
            'updater' => $invoiceItem->updater ? ['id' => $invoiceItem->updater->id, 'name' => $invoiceItem->updater->name] : null,
            'deleter' => $invoiceItem->deleter ? ['id' => $invoiceItem->deleter->id, 'name' => $invoiceItem->deleter->name] : null,
            'restorer' => $invoiceItem->restorer ? ['id' => $invoiceItem->restorer->id, 'name' => $invoiceItem->restorer->name] : null,
        ];
    }
}

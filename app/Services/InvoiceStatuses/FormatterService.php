<?php

namespace App\Services\InvoiceStatuses;

use App\Models\InvoiceStatus;

class FormatterService
{
    /**
     * Format a single invoice status with all data.
     *
     * @return array<string, mixed>
     */
    public function format(InvoiceStatus $invoiceStatus): array
    {
        return [
            'id' => $invoiceStatus->id,
            'title' => $invoiceStatus->title,
            'description' => $invoiceStatus->description,
            'background_colour' => $invoiceStatus->background_colour,
            'text_colour' => $invoiceStatus->text_colour,
            'meta' => $invoiceStatus->meta,
            'created_at' => $invoiceStatus->created_at,
            'updated_at' => $invoiceStatus->updated_at,
            'deleted_at' => $invoiceStatus->deleted_at,
            'restored_at' => $invoiceStatus->restored_at,
            'created_by' => $invoiceStatus->created_by,
            'updated_by' => $invoiceStatus->updated_by,
            'deleted_by' => $invoiceStatus->deleted_by,
            'restored_by' => $invoiceStatus->restored_by,
            'creator' => $invoiceStatus->creator ? ['id' => $invoiceStatus->creator->id, 'name' => $invoiceStatus->creator->name] : null,
            'updater' => $invoiceStatus->updater ? ['id' => $invoiceStatus->updater->id, 'name' => $invoiceStatus->updater->name] : null,
            'deleter' => $invoiceStatus->deleter ? ['id' => $invoiceStatus->deleter->id, 'name' => $invoiceStatus->deleter->name] : null,
            'restorer' => $invoiceStatus->restorer ? ['id' => $invoiceStatus->restorer->id, 'name' => $invoiceStatus->restorer->name] : null,
        ];
    }
}

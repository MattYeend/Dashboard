<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Models\QuoteItem;

class FormatterService
{
    /**
     * Format a single quote line with all data.
     *
     * @return array<string, mixed>
     */
    public function format(QuoteItem $item): array
    {
        return [
            'id' => $item->id,
            'quote_id' => $item->quote_id,
            'product_id' => $item->product_id,
            'position' => $item->position,
            'name' => $item->name,
            'description' => $item->description,
            'unit' => $item->unit,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'discount_percent' => $item->discount_percent,
            'tax_rate' => $item->tax_rate,
            'line_total' => $item->line_total,
            'line_tax' => $item->line_tax,
            'meta' => $item->meta,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
            'deleted_at' => $item->deleted_at,
            'restored_at' => $item->restored_at,
            'product' => $item->relationLoaded('product') && $item->product
                ? ['id' => $item->product->id, 'sku' => $item->product->sku, 'name' => $item->product->name]
                : null,
            'creator' => $item->relationLoaded('creator') && $item->creator
                ? ['id' => $item->creator->id, 'name' => $item->creator->name]
                : null,
            'updater' => $item->relationLoaded('updater') && $item->updater
                ? ['id' => $item->updater->id, 'name' => $item->updater->name]
                : null,
        ];
    }
}

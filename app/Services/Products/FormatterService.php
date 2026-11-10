<?php

declare(strict_types=1);

namespace App\Services\Products;

use App\Models\Product;
use App\Services\CurrencyFormatterService;

class FormatterService
{
    public function __construct(
        private readonly CurrencyFormatterService $currencyFormatter
    ) {}

    /**
     * Format a single product with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Product $product): array
    {
        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'description' => $product->description,
            'unit' => $product->unit,
            'unit_price' => $product->unit_price,
            'cost_price' => $product->cost_price,
            'tax_rate' => $product->tax_rate,
            'currency' => $product->currency,
            'formatted_unit_price' => $this->currencyFormatter->format(
                (float) $product->unit_price,
                $product->currency,
            ),
            'is_active' => $product->is_active,
            'meta' => $product->meta,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
            'deleted_at' => $product->deleted_at,
            'restored_at' => $product->restored_at,
            'creator' => $product->creator ? ['id' => $product->creator->id, 'name' => $product->creator->name] : null,
            'updater' => $product->updater ? ['id' => $product->updater->id, 'name' => $product->updater->name] : null,
            'deleter' => $product->deleter ? ['id' => $product->deleter->id, 'name' => $product->deleter->name] : null,
            'restorer' => $product->restorer ? ['id' => $product->restorer->id, 'name' => $product->restorer->name] : null,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Services\Quotes\TotalsService;

class DataPreparationService
{
    public function __construct(
        private readonly TotalsService $totals,
    ) {}

    /**
     * Prepare line data for creation.
     *
     * quote_id comes from the route-bound quote. The line total and tax are
     * always calculated here, never read from $data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data, Quote $quote, int $position): array
    {
        $product = $this->resolveProduct($data['product_id'] ?? null);

        $line = $this->priceLine([
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'] ?? $product?->unit_price,
            'discount_percent' => $data['discount_percent'] ?? 0,
            'tax_rate' => $data['tax_rate'] ?? $product->tax_rate ?? 20,
        ]);

        return [
            'quote_id' => $quote->id,
            'organisation_id' => $quote->organisation_id,
            'product_id' => $product?->id,
            'position' => $position,
            'name' => trim((string) ($data['name'] ?? $product?->name)),
            'description' => $data['description'] ?? $product?->description,
            'unit' => $data['unit'] ?? $product->unit ?? 'each',
            ...$line,
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare line data for update. Only allow-listed fields are copied,
     * and the line is repriced from the merged values.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, QuoteItem $item): array
    {
        $payload = [];

        foreach (['name', 'description', 'unit', 'meta'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $field === 'name' ? trim((string) $data[$field]) : $data[$field];
            }
        }

        if (array_key_exists('product_id', $data)) {
            $payload['product_id'] = $this->resolveProduct($data['product_id'])?->id;
        }

        $pricingKeys = ['quantity', 'unit_price', 'discount_percent', 'tax_rate'];

        if (array_intersect($pricingKeys, array_keys($data)) !== []) {
            $payload += $this->priceLine([
                'quantity' => $data['quantity'] ?? $item->quantity,
                'unit_price' => $data['unit_price'] ?? $item->unit_price,
                'discount_percent' => $data['discount_percent'] ?? $item->discount_percent,
                'tax_rate' => $data['tax_rate'] ?? $item->tax_rate,
            ]);
        }

        return $payload;
    }

    /**
     * Normalise the pricing inputs and calculate line total and tax.
     *
     * @param  array{quantity: mixed, unit_price: mixed, discount_percent: mixed, tax_rate: mixed}  $input
     * @return array<string, string>
     */
    private function priceLine(array $input): array
    {
        $quantity = $this->totals->normalise($input['quantity']);
        $unitPrice = $this->totals->normalise($input['unit_price']);
        $discount = $this->totals->normalise($input['discount_percent']);
        $taxRate = $this->totals->normalise($input['tax_rate']);

        $lineTotal = $this->totals->lineTotal($quantity, $unitPrice, $discount);

        return [
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_percent' => $discount,
            'tax_rate' => $taxRate,
            'line_total' => $lineTotal,
            'line_tax' => $this->totals->lineTax($lineTotal, $taxRate),
        ];
    }

    /**
     * Resolve a product inside the current tenant. The global scope on
     * Product makes a cross-tenant ID resolve to null.
     */
    private function resolveProduct(mixed $productId): ?Product
    {
        if ($productId === null || $productId === '') {
            return null;
        }

        return Product::query()->findOrFail((int) $productId);
    }
}

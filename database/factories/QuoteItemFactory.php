<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use App\Services\Quotes\TotalsService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Multitenancy\Models\Tenant;

/**
 * @extends Factory<QuoteItem>
 */
class QuoteItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<QuoteItem>
     */
    protected $model = QuoteItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totals = app(TotalsService::class);
        $quantity = (string) $this->faker->numberBetween(1, 10).'.00';
        $unitPrice = number_format($this->faker->randomFloat(2, 5, 500), 2, '.', '');
        $lineTotal = $totals->lineTotal($quantity, $unitPrice, '0');

        return [
            'organisation_id' => Tenant::current()?->id,
            'quote_id' => Quote::factory(),
            'product_id' => null,
            'position' => 1,
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'unit' => 'each',
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_percent' => '0.00',
            'tax_rate' => '20.00',
            'line_total' => $lineTotal,
            'line_tax' => $totals->lineTax($lineTotal, '20'),
            'meta' => null,
        ];
    }

    /**
     * Base the line on a product.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'name' => $product->name,
            'unit' => $product->unit,
            'unit_price' => $product->unit_price,
            'tax_rate' => $product->tax_rate,
        ]);
    }

    /**
     * State for a soft-deleted line.
     */
    public function deleted(?User $deletedBy = null): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => $deletedBy?->id,
        ]);
    }
}

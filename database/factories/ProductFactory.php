<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Multitenancy\Models\Tenant;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Product>
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 5, 2000);

        return [
            'organisation_id' => Tenant::current()?->id,
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-####-????')),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'unit' => $this->faker->randomElement(Product::UNITS),
            'unit_price' => $price,
            'cost_price' => round($price * 0.6, 2),
            'tax_rate' => 20.00,
            'currency' => 'GBP',
            'is_active' => true,
            'meta' => null,
        ];
    }

    /**
     * State for an inactive product.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * State for a soft-deleted product.
     */
    public function deleted(?User $deletedBy = null): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => $deletedBy?->id,
        ]);
    }
}
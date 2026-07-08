<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Plan '.strtoupper(Str::random(4));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'A test product plan.',
            'price_per_user_per_month' => 2500,
            'stripe_product_id' => null,
            'stripe_price_id' => null,
            'is_active' => true,
        ];
    }
}

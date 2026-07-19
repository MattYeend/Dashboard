<?php

namespace Database\Factories;

use App\Models\InvoiceStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceStatusFactory extends Factory
{
    protected $model = InvoiceStatus::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence(),
            'background_colour' => $this->faker->hexColor(),
            'text_colour' => $this->faker->hexColor(),
            'meta' => null,
        ];
    }

    /**
     * Indicate that the invoice status is soft-deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => User::factory(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\QuoteStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Multitenancy\Models\Tenant;

/**
 * @extends Factory<QuoteStatus>
 */
class QuoteStatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<QuoteStatus>
     */
    protected $model = QuoteStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Tenant::current()?->id,
            'title' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence(),
            'background_colour' => '#e2e8f0',
            'text_colour' => '#1a202c',
            'system_key' => null,
            'meta' => null,
        ];
    }

    /**
     * State for a protected system status.
     */
    public function system(string $key): static
    {
        return $this->state(fn (array $attributes) => [
            'system_key' => $key,
        ]);
    }
}

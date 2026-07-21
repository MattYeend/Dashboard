<?php

namespace Database\Factories;

use App\Models\RegistrationInterest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationInterest>
 */
class RegistrationInterestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<RegistrationInterest>
     */
    protected $model = RegistrationInterest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'company' => $this->faker->optional()->company(),
            'message' => $this->faker->optional()->sentence(),
            'meta' => null,
        ];
    }

    /**
     * State for a soft-deleted registration interest.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}

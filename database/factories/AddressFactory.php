<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Address>
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'addressable_type' => null,
            'addressable_id' => null,
            'address_line_one' => $this->faker->streetAddress(),
            'address_line_two' => null,
            'town' => null,
            'city' => $this->faker->city(),
            'county' => null,
            'postcode' => $this->faker->postcode(),
            'country' => 'United Kingdom',
            'is_primary' => true,
            'meta' => null,
        ];
    }

    /**
     * State for a secondary (non-primary) address.
     */
    public function secondary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => false,
        ]);
    }

    /**
     * State for a soft-deleted address.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }

    /**
     * Associate the address with a given morphable model.
     */
    public function forModel(Model $model): static
    {
        return $this->state(fn (array $attributes) => [
            'addressable_type' => $model->getMorphClass(),
            'addressable_id' => $model->getKey(),
        ]);
    }
}

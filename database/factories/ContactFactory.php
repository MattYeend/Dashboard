<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Contact>
     */
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contactable_type' => null,
            'contactable_id' => null,
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
        ];
    }

    /**
     * State for a contact with no phone number.
     *
     * @return static
     */
    public function withoutPhone(): static
    {
        return $this->state(fn(array $attributes) => [
            'phone' => null,
        ]);
    }

    /**
     * State for a contact with no email address.
     *
     * @return static
     */
    public function withoutEmail(): static
    {
        return $this->state(fn(array $attributes) => [
            'email' => null,
        ]);
    }

    /**
     * State for a soft-deleted contact.
     *
     * @return static
     */
    public function deleted(): static
    {
        return $this->state(fn(array $attributes) => [
            'deleted_at' => now(),
        ]);
    }

    /**
     * State for a UK-based contact.
     *
     * @return static
     */
    public function uk(): static
    {
        return $this->state(fn(array $attributes) => [
            'country' => 'United Kingdom',
        ]);
    }

    /**
     * State for a US-based contact.
     *
     * @return static
     */
    public function us(): static
    {
        return $this->state(fn(array $attributes) => [
            'country' => 'United States',
        ]);
    }

    /**
     * Associate the contact with a given morphable model.
     *
     * @param  Model $model
     *
     * @return static
     */
    public function forModel(Model $model): static
    {
        return $this->state(fn(array $attributes) => [
            'contactable_type' => $model->getMorphClass(),
            'contactable_id' => $model->getKey(),
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\TicketPriority;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketPriority>
 */
class TicketPriorityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TicketPriority>
     */
    protected $model = TicketPriority::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->words(2, true),
            'level' => $this->faker->numberBetween(1, 4),
            'background_colour' => $this->faker->hexColor(),
            'text_colour' => $this->faker->hexColor(),
            'meta' => null,
            'created_by' => null,
            'updated_by' => null,
            'deleted_by' => null,
            'restored_by' => null,
            'restored_at' => null,
        ];
    }

    /**
     * State for a ticket priority with meta data.
     *
     * @param  array<string, mixed>  $meta
     */
    public function withMeta(array $meta = []): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => $meta ?: ['key' => $this->faker->word(), 'value' => $this->faker->word()],
        ]);
    }

    /**
     * State for a ticket priority with a specific background and text colour.
     */
    public function withColours(string $backgroundColour, string $textColour): static
    {
        return $this->state(fn (array $attributes) => [
            'background_colour' => $backgroundColour,
            'text_colour' => $textColour,
        ]);
    }

    /**
     * State for a soft-deleted ticket priority.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => User::factory(),
        ]);
    }

    /**
     * State for a restored ticket priority.
     */
    public function restored(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => null,
            'restored_at' => now(),
            'restored_by' => User::factory(),
        ]);
    }

    /**
     * State for a ticket priority created by a specific user.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    /**
     * State for the "Low" priority.
     */
    public function low(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Low',
            'level' => 1,
            'background_colour' => '#e2e8f0',
            'text_colour' => '#1a202c',
        ]);
    }

    /**
     * State for the "Medium" priority.
     */
    public function medium(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Medium',
            'level' => 2,
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    }

    /**
     * State for the "High" priority.
     */
    public function high(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'High',
            'level' => 3,
            'background_colour' => '#feebc8',
            'text_colour' => '#7b341e',
        ]);
    }

    /**
     * State for the "Critical" priority.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Critical',
            'level' => 4,
            'background_colour' => '#fed7d7',
            'text_colour' => '#742a2a',
        ]);
    }
}

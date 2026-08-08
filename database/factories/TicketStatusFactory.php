<?php

namespace Database\Factories;

use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketStatus>
 */
class TicketStatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TicketStatus>
     */
    protected $model = TicketStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->words(2, true),
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
     * State for a ticket status with meta data.
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
     * State for a ticket status with a specific background and text colour.
     */
    public function withColours(string $backgroundColour, string $textColour): static
    {
        return $this->state(fn (array $attributes) => [
            'background_colour' => $backgroundColour,
            'text_colour' => $textColour,
        ]);
    }

    /**
     * State for a soft-deleted ticket status.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => User::factory(),
        ]);
    }

    /**
     * State for a restored ticket status.
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
     * State for a ticket status created by a specific user.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    /**
     * State for the "Open" status.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Open',
            'background_colour' => '#e2e8f0',
            'text_colour' => '#1a202c',
        ]);
    }

    /**
     * State for the "In Progress" status.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'In Progress',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    }

    /**
     * State for the "On Hold" status.
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'On Hold',
            'background_colour' => '#feebc8',
            'text_colour' => '#7b341e',
        ]);
    }

    /**
     * State for the "Pending Customer" status.
     */
    public function pendingCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Pending Customer',
            'background_colour' => '#fefcbf',
            'text_colour' => '#744210',
        ]);
    }

    /**
     * State for the "Resolved" status.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Resolved',
            'background_colour' => '#c6f6d5',
            'text_colour' => '#22543d',
        ]);
    }

    /**
     * State for the "Closed" status.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Closed',
            'background_colour' => '#e2e8f0',
            'text_colour' => '#718096',
        ]);
    }

    /**
     * State for the "Reopened" status.
     */
    public function reopened(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Reopened',
            'background_colour' => '#fed7d7',
            'text_colour' => '#742a2a',
        ]);
    }
}

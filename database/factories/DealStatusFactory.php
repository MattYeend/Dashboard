<?php

namespace Database\Factories;

use App\Models\DealStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DealStatus>
 */
class DealStatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<DealStatus>
     */
    protected $model = DealStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence(),
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
     * State for a deal status with no description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }

    /**
     * State for a deal status with meta data.
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
     * State for a deal status with a specific background and text colour.
     */
    public function withColours(string $backgroundColour, string $textColour): static
    {
        return $this->state(fn (array $attributes) => [
            'background_colour' => $backgroundColour,
            'text_colour' => $textColour,
        ]);
    }

    /**
     * State for a soft-deleted deal status.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => User::factory(),
        ]);
    }

    /**
     * State for a restored deal status.
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
     * State for a deal status created by a specific user.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    /**
     * State for the "New" status.
     */
    public function newStatus(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'New',
            'description' => 'Deal has been created and has not yet been reviewed.',
            'background_colour' => '#e2e8f0',
            'text_colour' => '#1a202c',
        ]);
    }

    /**
     * State for the "Qualified" status.
     */
    public function qualified(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Qualified',
            'description' => 'Deal has been reviewed and meets the criteria to be pursued.',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    }

    /**
     * State for the "Proposal Sent" status.
     */
    public function proposalSent(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Proposal Sent',
            'description' => 'A proposal or quote has been sent to the client.',
            'background_colour' => '#d6bcfa',
            'text_colour' => '#553c9a',
        ]);
    }

    /**
     * State for the "Negotiation" status.
     */
    public function negotiation(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Negotiation',
            'description' => 'Terms are being discussed and negotiated with the client.',
            'background_colour' => '#fefcbf',
            'text_colour' => '#744210',
        ]);
    }

    /**
     * State for the "Won" status.
     */
    public function won(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Won',
            'description' => 'Deal has been agreed and closed successfully.',
            'background_colour' => '#c6f6d5',
            'text_colour' => '#22543d',
        ]);
    }

    /**
     * State for the "Lost" status.
     */
    public function lost(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Lost',
            'description' => 'Deal did not proceed and has been closed unsuccessfully.',
            'background_colour' => '#fed7d7',
            'text_colour' => '#742a2a',
        ]);
    }

    /**
     * State for the "On Hold" status.
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'On Hold',
            'description' => 'Deal has been paused pending further information.',
            'background_colour' => '#feb2b2',
            'text_colour' => '#822727',
        ]);
    }
}

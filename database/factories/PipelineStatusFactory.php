<?php

namespace Database\Factories;

use App\Models\PipelineStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PipelineStatus>
 */
class PipelineStatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<PipelineStatus>
     */
    protected $model = PipelineStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
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
     * State for a pipeline status with no description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }

    /**
     * State for a pipeline status with meta data.
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
     * State for a pipeline status with a specific background and text colour.
     */
    public function withColours(string $backgroundColour, string $textColour): static
    {
        return $this->state(fn (array $attributes) => [
            'background_colour' => $backgroundColour,
            'text_colour' => $textColour,
        ]);
    }

    /**
     * State for a soft-deleted pipeline status.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => User::factory(),
        ]);
    }

    /**
     * State for a restored pipeline status.
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
     * State for a pipeline status created by a specific user.
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
            'description' => 'Deal is active and progressing through the pipeline.',
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
            'description' => 'Lead has been assessed and meets the criteria to proceed.',
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
            'description' => 'A proposal or quote has been sent to the prospect.',
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
            'description' => 'Terms are being discussed and agreed with the prospect.',
            'background_colour' => '#fefcbf',
            'text_colour' => '#744210',
        ]);
    }

    /**
     * State for the "On Hold" status.
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'On Hold',
            'description' => 'Deal has been paused pending further information or decisions.',
            'background_colour' => '#feebc8',
            'text_colour' => '#7b341e',
        ]);
    }

    /**
     * State for the "Won" status.
     */
    public function won(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Won',
            'description' => 'Deal was successfully closed with the prospect.',
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
     * State for the "Abandoned" status.
     */
    public function abandoned(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Abandoned',
            'description' => 'Prospect went unresponsive and the deal was withdrawn.',
            'background_colour' => '#e2e8f0',
            'text_colour' => '#718096',
        ]);
    }
}

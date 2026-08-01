<?php

namespace Database\Factories;

use App\Models\Pipeline;
use App\Models\PipelineStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pipeline>
 */
class PipelineFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Pipeline>
     */
    protected $model = Pipeline::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->words(2, true).' Pipeline',
            'description' => $this->faker->boolean(70) ? $this->faker->sentence() : null,
            'is_default' => false,
            'status_id' => null,
            'assigned_to' => User::factory(),
            'meta' => null,
        ];
    }

    /**
     * Indicate that the pipeline is the default.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Associate the pipeline with a given status.
     */
    public function forStatus(PipelineStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => $status->id,
        ]);
    }

    /**
     * Associate the pipeline with a given assignee.
     */
    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $user->id,
        ]);
    }

    /**
     * Indicate that the pipeline has no assignee.
     */
    public function unassigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => null,
        ]);
    }

    /**
     * State for a soft-deleted pipeline.
     */
    public function deleted(?User $deletedBy = null): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => $deletedBy?->id,
        ]);
    }
}

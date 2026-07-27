<?php

namespace Database\Factories;

use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PipelineStage>
 */
class PipelineStageFactory extends Factory
{
    protected $model = PipelineStage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pipeline_id' => Pipeline::factory(),
            'title' => ucfirst($this->faker->words(2, true)),
            'description' => $this->faker->sentence(8),
            'position' => 0,
            'background_colour' => $this->faker->safeHexColor(),
            'text_colour' => $this->faker->safeHexColor(),
            'is_won' => false,
            'is_lost' => false,
            'meta' => null,
        ];
    }

    /**
     * State for a stage that marks a pipeline as won.
     */
    public function won(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Won',
            'is_won' => true,
            'is_lost' => false,
        ]);
    }

    /**
     * State for a stage that marks a pipeline as lost.
     */
    public function lost(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Lost',
            'is_won' => false,
            'is_lost' => true,
        ]);
    }

    /**
     * Indicate that the pipeline stage is soft-deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => User::factory(),
        ]);
    }
}

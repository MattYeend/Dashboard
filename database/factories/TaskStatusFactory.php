<?php

namespace Database\Factories;

use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskStatus>
 */
class TaskStatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TaskStatus>
     */
    protected $model = TaskStatus::class;

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
     * State for a task status with no description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }

    /**
     * State for a task status with meta data.
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
     * State for a task status with a specific background and text colour.
     */
    public function withColours(string $backgroundColour, string $textColour): static
    {
        return $this->state(fn (array $attributes) => [
            'background_colour' => $backgroundColour,
            'text_colour' => $textColour,
        ]);
    }

    /**
     * State for a soft-deleted task status.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => User::factory(),
        ]);
    }

    /**
     * State for a restored task status.
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
     * State for a task status created by a specific user.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    /**
     * State for the "To Do" status.
     */
    public function todo(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'To Do',
            'description' => 'Task has been created but work has not yet started.',
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
            'description' => 'Task is actively being worked on.',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    }

    /**
     * State for the "Done" status.
     */
    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Done',
            'description' => 'Task has been completed and signed off.',
            'background_colour' => '#c6f6d5',
            'text_colour' => '#22543d',
        ]);
    }

    /**
     * State for the "Blocked" status.
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Blocked',
            'description' => 'Task cannot proceed due to a dependency or issue.',
            'background_colour' => '#fed7d7',
            'text_colour' => '#742a2a',
        ]);
    }

    /**
     * State for the "Cancelled" status.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Cancelled',
            'description' => 'Task has been cancelled and will not be completed.',
            'background_colour' => '#e2e8f0',
            'text_colour' => '#718096',
        ]);
    }
}

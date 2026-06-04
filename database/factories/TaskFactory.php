<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Task '.$this->faker->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'due_date' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'assigned_date' => $this->faker->optional()->dateTimeBetween('-7 days', 'now'),
            'assigned_to' => null,
            'status_id' => null,
            'meta' => null,
        ];
    }

    /**
     * State for a soft-deleted task.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }

    /**
     * State for a task with an assigned user.
     */
    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $user->id,
            'assigned_date' => now(),
        ]);
    }

    /**
     * State for a task with a status.
     */
    public function withStatus(TaskStatus $taskStatus): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => $taskStatus->id,
        ]);
    }
}

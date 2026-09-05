<?php

namespace Database\Factories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'type' => fake()->randomElement(['orders', 'companies', 'users']),
            'format' => fake()->randomElement(['pdf', 'csv', 'xlsx']),
            'filters' => [],
            'is_scheduled' => false,
            'schedule_frequency' => null,
            'schedule_time' => null,
            'recipients' => null,
            'last_run_at' => null,
            'next_run_at' => null,
            'created_by' => null,
            'updated_by' => null,
            'deleted_by' => null,
            'restored_by' => null,
            'restored_at' => null,
        ];
    }

    /**
     * Indicate that the report is scheduled to run automatically.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_scheduled' => true,
            'schedule_frequency' => 'daily',
            'schedule_time' => '06:00',
            'recipients' => [fake()->safeEmail()],
            'next_run_at' => now()->addDay(),
        ]);
    }

    /**
     * Indicate that the report is due to run now.
     */
    public function due(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_scheduled' => true,
            'schedule_frequency' => 'daily',
            'schedule_time' => '06:00',
            'recipients' => [fake()->safeEmail()],
            'next_run_at' => now()->subMinute(),
        ]);
    }

    /**
     * Indicate that the report was soft deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}

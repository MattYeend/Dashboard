<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CourseDifficulty;
use App\Models\Course;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::current()->id ?? Organisation::query()->oldest('id')->value('id'),
            'title' => 'Introduction to Laravel',
            'slug' => 'introduction-to-laravel-'.Str::lower(Str::random(6)),
            'summary' => 'Learn the fundamentals of the Laravel framework.',
            'description' => '<p>Routing, controllers, Eloquent and Blade.</p>',
            'difficulty' => CourseDifficulty::Beginner,
            'estimated_duration_minutes' => 240,
            'published_at' => now()->subDay(),
        ];
    }

    /**
     * Indicate that the course is soft deleted.
     */
    public function deleted(): static
    {
        return $this->state(['deleted_at' => now()]);
    }

    /**
     * Indicate that the course is an unpublished draft.
     */
    public function draft(): static
    {
        return $this->state(['published_at' => null]);
    }
}

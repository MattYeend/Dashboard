<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::current()?->id ?? Organisation::query()->oldest('id')->value('id'),
            'course_id' => Course::factory(),
            'title' => 'Routing basics',
            'slug' => 'routing-basics-'.Str::lower(Str::random(6)),
            'summary' => 'How Laravel maps URLs to controllers.',
            'content' => '<p>Routes are defined in <code>routes/web.php</code>.</p>',
            'position' => 1,
            'duration_minutes' => 20,
            'is_preview' => false,
            'published_at' => now()->subDay(),
        ];
    }

    /**
     * Indicate that the lesson is soft deleted.
     */
    public function deleted(): static
    {
        return $this->state(['deleted_at' => now()]);
    }

    /**
     * Indicate that the lesson is a free preview.
     */
    public function preview(): static
    {
        return $this->state(['is_preview' => true]);
    }
}

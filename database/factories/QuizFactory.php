<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Course;
use App\Models\Organisation;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
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
            'lesson_id' => null,
            'title' => 'Laravel fundamentals check',
            'slug' => 'laravel-fundamentals-check-'.Str::lower(Str::random(6)),
            'description' => '<p>A short check of the basics.</p>',
            'pass_mark_percentage' => 70,
            'shuffle_questions' => false,
            'show_correct_answers' => true,
            'published_at' => now()->subDay(),
        ];
    }

    /**
     * Indicate that the quiz is soft deleted.
     */
    public function deleted(): static
    {
        return $this->state(['deleted_at' => now()]);
    }

    /**
     * Indicate that the quiz is an unpublished draft.
     */
    public function draft(): static
    {
        return $this->state(['published_at' => null]);
    }

    /**
     * Attach the given questions in order.
     *
     * @param  iterable<int, Question>  $questions
     */
    public function withQuestions(iterable $questions): static
    {
        return $this->afterCreating(function (Quiz $quiz) use ($questions): void {
            $payload = [];

            foreach ($questions as $index => $question) {
                $payload[$question->id] = ['position' => $index + 1];
            }

            $quiz->questions()->sync($payload);
        });
    }
}

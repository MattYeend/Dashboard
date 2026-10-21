<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Multitenancy\Models\Tenant;

/**
 * @extends Factory<QuizQuestion>
 */
class QuizQuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<QuizQuestion>
     */
    protected $model = QuizQuestion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            // Inherit the organisation from the parent quiz so the pivot can never
            // disagree with it; fall back to the current tenant if the quiz has none.
            'organisation_id' => fn (array $attributes): ?int => Quiz::find($attributes['quiz_id'])?->organisation_id
                ?? Tenant::current()?->id,
            'question_id' => Question::factory(),
            'position' => 1,
            'points' => null,
        ];
    }

    /**
     * Link the question to a specific quiz.
     */
    public function forQuiz(Quiz $quiz): static
    {
        return $this->state(fn (array $attributes) => [
            'quiz_id' => $quiz->id,
            'organisation_id' => $quiz->organisation_id,
        ]);
    }

    /**
     * Link a specific question to the quiz.
     */
    public function forQuestion(Question $question): static
    {
        return $this->state(fn (array $attributes) => [
            'question_id' => $question->id,
        ]);
    }

    /**
     * Set the position of the question within the quiz.
     */
    public function atPosition(int $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }

    /**
     * Override the points awarded for this question in the quiz.
     */
    public function withPoints(int $points): static
    {
        return $this->state(fn (array $attributes) => [
            'points' => $points,
        ]);
    }
}

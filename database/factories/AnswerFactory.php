<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Answer>
 */
class AnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'organisation_id' => fn (array $attributes): int => Question::query()
                ->findOrFail($attributes['question_id'])
                ->organisation_id,
            'answer' => 'Sample answer',
            'is_correct' => false,
            'feedback' => null,
            'position' => 1,
        ];
    }

    /**
     * Indicate that the answer is soft deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn (): array => ['deleted_at' => now()]);
    }
}

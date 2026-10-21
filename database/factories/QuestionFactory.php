<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\QuestionType;
use App\Models\Organisation;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
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
            'questionable_type' => (new Quiz)->getMorphClass(),
            'questionable_id' => Quiz::factory(),
            'question' => rtrim($this->faker->sentence(8), '.').'?',
            'type' => QuestionType::SingleChoice->value,
            'explanation' => $this->faker->sentence(12),
            'points' => 1,
            'position' => 0,
            'is_required' => true,
            'meta' => null,
        ];
    }

    /**
     * Set the question type.
     */
    public function ofType(QuestionType $type): static
    {
        return $this->state(['type' => $type->value]);
    }

    /**
     * Attach the question to the given owning model.
     */
    public function forQuestionable(Model $questionable): static
    {
        return $this->state([
            'organisation_id' => $questionable->organisation_id,
            'questionable_type' => $questionable->getMorphClass(),
            'questionable_id' => $questionable->getKey(),
        ]);
    }

    /**
     * Set the question's position within its owner.
     */
    public function atPosition(int $position): static
    {
        return $this->state(['position' => $position]);
    }

    /**
     * Set the points awarded for the question.
     */
    public function worth(int $points): static
    {
        return $this->state(['points' => $points]);
    }

    /**
     * Indicate that the question is optional.
     */
    public function optional(): static
    {
        return $this->state(['is_required' => false]);
    }

    /**
     * Indicate that the question has no explanation.
     */
    public function withoutExplanation(): static
    {
        return $this->state(['explanation' => null]);
    }

    /**
     * Indicate that the question is soft deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => User::factory(),
        ]);
    }
}

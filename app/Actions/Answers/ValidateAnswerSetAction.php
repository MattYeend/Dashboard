<?php

declare(strict_types=1);

namespace App\Actions\Answers;

use App\Enums\QuestionType;
use App\Models\Question;
use Illuminate\Validation\ValidationException;

class ValidateAnswerSetAction
{
    /**
     * Enforce the hard rules that must never be broken by a write.
     *
     * Call this inside a transaction that holds a lock on the question.
     *
     * @throws ValidationException
     */
    public function assertNotInvalid(Question $question): void
    {
        $answers = $question->answers()->get();
        $correct = $answers->where('is_correct', true)->count();

        $error = match ($question->type) {
            QuestionType::SingleChoice => $correct > 1
                ? 'A single choice question can only have one correct answer.'
                : null,
            QuestionType::TrueFalse => match (true) {
                $answers->count() > 2 => 'A true or false question can only have two answers.',
                $correct > 1 => 'A true or false question can only have one correct answer.',
                default => null,
            },
            default => null,
        };

        if ($error !== null) {
            throw ValidationException::withMessages(['is_correct' => $error]);
        }
    }

    /**
     * Get the completeness problems that stop a question being ready.
     *
     * @return list<string>
     */
    public function problems(Question $question): array
    {
        $answers = $question->answers()->get();
        $total = $answers->count();
        $correct = $answers->where('is_correct', true)->count();

        return match ($question->type) {
            QuestionType::SingleChoice => $this->choiceProblems($total, $correct === 1, 'Mark exactly one answer as correct.'),
            QuestionType::MultipleChoice => $this->choiceProblems($total, $correct >= 1, 'Mark at least one answer as correct.'),
            QuestionType::TrueFalse => match (true) {
                $total !== 2 => ['Add exactly two answers.'],
                $correct !== 1 => ['Mark exactly one answer as correct.'],
                default => [],
            },
            QuestionType::ShortText => $total === 0 ? ['Add at least one accepted answer.'] : [],
        };
    }

    /**
     * Shared completeness check for the choice question types.
     *
     * @return list<string>
     */
    protected function choiceProblems(int $total, bool $hasValidCorrect, string $message): array
    {
        return match (true) {
            $total < 2 => ['Add at least two answers.'],
            ! $hasValidCorrect => [$message],
            default => [],
        };
    }
}

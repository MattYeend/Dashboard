<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\Models\Quiz;

/**
 * Attaches, orders and re-scores the questions on a quiz.
 */
class QuestionAttachmentService
{
    /**
     * Replace the quiz's questions with the given selections, in array order.
     *
     * @param  array<int, array{question_id: int, points: int|null}>  $selections
     */
    public function sync(Quiz $quiz, array $selections): void
    {
        $payload = [];

        foreach (array_values($selections) as $index => $selection) {
            $payload[$selection['question_id']] = [
                'organisation_id' => $quiz->organisation_id,
                'position' => $index + 1,
                'points' => $selection['points'],
            ];
        }

        $quiz->questions()->sync($payload);
    }
}

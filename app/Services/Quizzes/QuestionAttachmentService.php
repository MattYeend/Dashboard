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
     * Position follows the order of the array, so the client never sets it directly.
     *
     * @param  array<int, array{question_id: int, points: int|null}>  $selections
     */
    public function sync(Quiz $quiz, array $selections): void
    {
        $payload = [];

        foreach (array_values($selections) as $index => $selection) {
            $payload[(int) $selection['question_id']] = [
                'position' => $index + 1,
                'points' => $selection['points'] ?? null,
            ];
        }

        $quiz->questions()->sync($payload);
    }
}

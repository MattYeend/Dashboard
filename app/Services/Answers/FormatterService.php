<?php

declare(strict_types=1);

namespace App\Services\Answers;

use App\Models\Answer;

class FormatterService
{
    /**
     * Format a single answer with all data.
     *
     * The correctness flag and feedback are only included when the caller
     * is allowed to see the answer key, so they never reach the browser
     * for anyone else.
     *
     * @return array<string, mixed>
     */
    public function format(Answer $answer, bool $canViewCorrect = false): array
    {
        $data = [
            'id' => $answer->id,
            'organisation_id' => $answer->organisation_id,
            'question_id' => $answer->question_id,
            'answer' => $answer->answer,
            'position' => $answer->position,
            'meta' => $answer->meta,
            'created_by' => $answer->created_by,
            'updated_by' => $answer->updated_by,
            'deleted_by' => $answer->deleted_by,
            'restored_by' => $answer->restored_by,
            'created_at' => $answer->created_at,
            'updated_at' => $answer->updated_at,
            'deleted_at' => $answer->deleted_at,
            'restored_at' => $answer->restored_at,
            'question' => $answer->relationLoaded('question') && $answer->question
                ? ['id' => $answer->question->id, 'question' => $answer->question->question]
                : null,
            'creator' => $answer->creator ? ['id' => $answer->creator->id, 'name' => $answer->creator->name] : null,
            'updater' => $answer->updater ? ['id' => $answer->updater->id, 'name' => $answer->updater->name] : null,
            'deleter' => $answer->deleter ? ['id' => $answer->deleter->id, 'name' => $answer->deleter->name] : null,
            'restorer' => $answer->restorer ? ['id' => $answer->restorer->id, 'name' => $answer->restorer->name] : null,
        ];

        if ($canViewCorrect) {
            $data['is_correct'] = $answer->is_correct;
            $data['feedback'] = $answer->feedback;
        }

        return $data;
    }
}

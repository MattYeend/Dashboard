<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\Models\Quiz;

class FormatterService
{
    /**
     * Format a quiz for editors, including correct answers.
     *
     * @return array<string, mixed>
     */
    public function format(Quiz $quiz): array
    {
        return array_merge($this->base($quiz), [
            'questions' => $quiz->relationLoaded('questions')
                ? $quiz->questions->map(fn ($question) => [
                    'id' => $question->id,
                    'prompt' => $question->prompt,
                    'type' => $question->type->value,
                    'type_label' => $question->type->label(),
                    'explanation' => $question->explanation,
                    'position' => $question->pivot->position,
                    'default_points' => $question->points,
                    'points' => $question->pivot->points,
                    'effective_points' => $question->pivot->points ?? $question->points,
                    'answers' => $question->relationLoaded('answers')
                        ? $question->answers->map(fn ($answer) => [
                            'id' => $answer->id,
                            'body' => $answer->body,
                            'is_correct' => (bool) $answer->is_correct,
                        ])->values()->all()
                        : [],
                ])->values()->all()
                : null,
            'created_by' => $quiz->created_by,
            'updated_by' => $quiz->updated_by,
            'deleted_by' => $quiz->deleted_by,
            'restored_by' => $quiz->restored_by,
            'deleted_at' => $quiz->deleted_at,
            'restored_at' => $quiz->restored_at,
            'creator' => $this->person($quiz->creator),
            'updater' => $this->person($quiz->updater),
            'deleter' => $this->person($quiz->deleter),
            'restorer' => $this->person($quiz->restorer),
        ]);
    }

    /**
     * Format a quiz for learners. Omits is_correct and explanation so
     * answers cannot be read from the page payload.
     *
     * @return array<string, mixed>
     */
    public function formatForLearner(Quiz $quiz): array
    {
        return array_merge($this->base($quiz), [
            'questions' => $quiz->relationLoaded('questions')
                ? $quiz->questions->map(fn ($question) => [
                    'id' => $question->id,
                    'prompt' => $question->prompt,
                    'type' => $question->type->value,
                    'type_label' => $question->type->label(),
                    'position' => $question->pivot->position,
                    'effective_points' => $question->pivot->points ?? $question->points,
                    'answers' => $question->relationLoaded('answers')
                        ? $question->answers->map(fn ($answer) => [
                            'id' => $answer->id,
                            'body' => $answer->body,
                        ])->values()->all()
                        : [],
                ])->values()->all()
                : null,
        ]);
    }

    /**
     * Attributes common to both formats.
     *
     * @return array<string, mixed>
     */
    protected function base(Quiz $quiz): array
    {
        return [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'slug' => $quiz->slug,
            'description' => $quiz->description,
            'course_id' => $quiz->course_id,
            'lesson_id' => $quiz->lesson_id,
            'pass_mark_percentage' => $quiz->pass_mark_percentage,
            'time_limit_minutes' => $quiz->time_limit_minutes,
            'max_attempts' => $quiz->max_attempts,
            'shuffle_questions' => $quiz->shuffle_questions,
            'show_correct_answers' => $quiz->show_correct_answers,
            'published_at' => $quiz->published_at,
            'is_published' => $quiz->isPublished(),
            'meta' => $quiz->meta,
            'created_at' => $quiz->created_at,
            'updated_at' => $quiz->updated_at,
            'course' => $quiz->course ? ['id' => $quiz->course->id, 'title' => $quiz->course->title] : null,
            'lesson' => $quiz->lesson ? ['id' => $quiz->lesson->id, 'title' => $quiz->lesson->title] : null,
            'questions_count' => $quiz->questions_count
                ?? ($quiz->relationLoaded('questions') ? $quiz->questions->count() : null),
        ];
    }

    /**
     * @return array{id: int, name: string}|null
     */
    protected function person(mixed $user): ?array
    {
        return $user ? ['id' => $user->id, 'name' => $user->name] : null;
    }
}

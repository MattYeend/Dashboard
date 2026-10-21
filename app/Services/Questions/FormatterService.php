<?php

declare(strict_types=1);

namespace App\Services\Questions;

use App\Models\Question;
use Illuminate\Database\Eloquent\Model;

class FormatterService
{
    /**
     * Format a single question with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Question $question): array
    {
        return [
            'id' => $question->id,
            'questionable_type' => $question->questionable_type,
            'questionable_id' => $question->questionable_id,
            'question' => $question->question,
            'type' => $question->type->value,
            'type_label' => $question->type->label(),
            'explanation' => $question->explanation,
            'points' => $question->points,
            'position' => $question->position,
            'is_required' => $question->is_required,
            'meta' => $question->meta,
            'created_by' => $question->created_by,
            'updated_by' => $question->updated_by,
            'deleted_by' => $question->deleted_by,
            'restored_by' => $question->restored_by,
            'created_at' => $question->created_at,
            'updated_at' => $question->updated_at,
            'deleted_at' => $question->deleted_at,
            'restored_at' => $question->restored_at,
            'questionable' => $question->questionable
                ? [
                    'id' => $question->questionable->getKey(),
                    'label' => $this->label($question->questionable),
                ]
                : null,
            'creator' => $question->creator ? ['id' => $question->creator->id, 'name' => $question->creator->name] : null,
            'updater' => $question->updater ? ['id' => $question->updater->id, 'name' => $question->updater->name] : null,
            'deleter' => $question->deleter ? ['id' => $question->deleter->id, 'name' => $question->deleter->name] : null,
            'restorer' => $question->restorer ? ['id' => $question->restorer->id, 'name' => $question->restorer->name] : null,
        ];
    }

    /**
     * Resolve a human-readable label for the owning model.
     */
    private function label(Model $model): string
    {
        return (string) (data_get($model, 'title')
            ?? data_get($model, 'name')
            ?? class_basename($model).' #'.$model->getKey());
    }
}

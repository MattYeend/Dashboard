<?php

namespace App\Services\TaskStatuses;

use App\Models\TaskStatus;

class FormatterService
{
    /**
     * Format a single task status with all data.
     *
     * @return array<string, mixed>
     */
    public function format(TaskStatus $taskStatus): array
    {
        return [
            'id' => $taskStatus->id,
            'title' => $taskStatus->title,
            'description' => $taskStatus->description,
            'background_colour' => $taskStatus->background_colour,
            'text_colour' => $taskStatus->text_colour,
            'meta' => $taskStatus->meta,
            'created_at' => $taskStatus->created_at,
            'updated_at' => $taskStatus->updated_at,
            'deleted_at' => $taskStatus->deleted_at,
            'restored_at' => $taskStatus->restored_at,
            'created_by' => $taskStatus->created_by,
            'updated_by' => $taskStatus->updated_by,
            'deleted_by' => $taskStatus->deleted_by,
            'restored_by' => $taskStatus->restored_by,
        ];
    }
}

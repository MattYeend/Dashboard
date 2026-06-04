<?php

namespace App\Services\Tasks;

use App\Models\Task;

class FormatterService
{
    /**
     * Format a single task with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'due_date' => $task->due_date,
            'assigned_date' => $task->assigned_date,
            'assigned_to' => $task->assigned_to,
            'status_id' => $task->status_id,
            'meta' => $task->meta,
            'created_at' => $task->created_at,
            'updated_at' => $task->updated_at,
            'deleted_at' => $task->deleted_at,
            'restored_at' => $task->restored_at,
            'created_by' => $task->created_by,
            'updated_by' => $task->updated_by,
            'deleted_by' => $task->deleted_by,
            'restored_by' => $task->restored_by,
            'assignee' => $task->assignee ? [
                'id' => $task->assignee->id,
                'name' => $task->assignee->name,
            ] : null,
            'status' => $task->status ? [
                'id' => $task->status->id,
                'title' => $task->status->title,
                'background_colour' => $task->status->background_colour,
                'text_colour' => $task->status->text_colour,
            ] : null,
        ];
    }
}

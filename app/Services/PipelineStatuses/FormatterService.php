<?php

namespace App\Services\PipelineStatuses;

use App\Models\PipelineStatus;

class FormatterService
{
    /**
     * Format a single pipeline status with all data.
     *
     * @return array<string, mixed>
     */
    public function format(PipelineStatus $pipelineStatus): array
    {
        return [
            'id' => $pipelineStatus->id,
            'title' => $pipelineStatus->title,
            'description' => $pipelineStatus->description,
            'background_colour' => $pipelineStatus->background_colour,
            'text_colour' => $pipelineStatus->text_colour,
            'meta' => $pipelineStatus->meta,
            'created_at' => $pipelineStatus->created_at,
            'updated_at' => $pipelineStatus->updated_at,
            'deleted_at' => $pipelineStatus->deleted_at,
            'restored_at' => $pipelineStatus->restored_at,
            'created_by' => $pipelineStatus->created_by,
            'updated_by' => $pipelineStatus->updated_by,
            'deleted_by' => $pipelineStatus->deleted_by,
            'restored_by' => $pipelineStatus->restored_by,
            'creator' => $pipelineStatus->creator ? ['id' => $pipelineStatus->creator->id, 'name' => $pipelineStatus->creator->name] : null,
            'updater' => $pipelineStatus->updater ? ['id' => $pipelineStatus->updater->id, 'name' => $pipelineStatus->updater->name] : null,
            'deleter' => $pipelineStatus->deleter ? ['id' => $pipelineStatus->deleter->id, 'name' => $pipelineStatus->deleter->name] : null,
            'restorer' => $pipelineStatus->restorer ? ['id' => $pipelineStatus->restorer->id, 'name' => $pipelineStatus->restorer->name] : null,
        ];
    }
}
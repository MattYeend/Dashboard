<?php

namespace App\Services\PipelineStages;

use App\Models\PipelineStage;

class FormatterService
{
    /**
     * Format a single pipeline stage with all data.
     *
     * @return array<string, mixed>
     */
    public function format(PipelineStage $pipelineStage): array
    {
        return [
            'id' => $pipelineStage->id,
            'pipeline_id' => $pipelineStage->pipeline_id,
            'title' => $pipelineStage->title,
            'description' => $pipelineStage->description,
            'position' => $pipelineStage->position,
            'background_colour' => $pipelineStage->background_colour,
            'text_colour' => $pipelineStage->text_colour,
            'is_won' => $pipelineStage->is_won,
            'is_lost' => $pipelineStage->is_lost,
            'meta' => $pipelineStage->meta,
            'created_at' => $pipelineStage->created_at,
            'updated_at' => $pipelineStage->updated_at,
            'deleted_at' => $pipelineStage->deleted_at,
            'restored_at' => $pipelineStage->restored_at,
            'created_by' => $pipelineStage->created_by,
            'updated_by' => $pipelineStage->updated_by,
            'deleted_by' => $pipelineStage->deleted_by,
            'restored_by' => $pipelineStage->restored_by,
            'pipeline' => $pipelineStage->pipeline ? [
                'id' => $pipelineStage->pipeline->id,
                'title' => $pipelineStage->pipeline->title,
            ] : null,
            'creator' => $pipelineStage->creator ? ['id' => $pipelineStage->creator->id, 'name' => $pipelineStage->creator->name] : null,
            'updater' => $pipelineStage->updater ? ['id' => $pipelineStage->updater->id, 'name' => $pipelineStage->updater->name] : null,
            'deleter' => $pipelineStage->deleter ? ['id' => $pipelineStage->deleter->id, 'name' => $pipelineStage->deleter->name] : null,
            'restorer' => $pipelineStage->restorer ? ['id' => $pipelineStage->restorer->id, 'name' => $pipelineStage->restorer->name] : null,
        ];
    }
}
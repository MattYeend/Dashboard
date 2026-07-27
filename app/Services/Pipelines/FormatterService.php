<?php

namespace App\Services\Pipelines;

use App\Models\Pipeline;

class FormatterService
{
    /**
     * Format a single pipeline with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Pipeline $pipeline): array
    {
        return [
            'id' => $pipeline->id,
            'title' => $pipeline->title,
            'description' => $pipeline->description,
            'is_default' => $pipeline->is_default,
            'status_id' => $pipeline->status_id,
            'meta' => $pipeline->meta,
            'created_at' => $pipeline->created_at,
            'updated_at' => $pipeline->updated_at,
            'deleted_at' => $pipeline->deleted_at,
            'restored_at' => $pipeline->restored_at,
            'created_by' => $pipeline->created_by,
            'updated_by' => $pipeline->updated_by,
            'deleted_by' => $pipeline->deleted_by,
            'restored_by' => $pipeline->restored_by,
            'status' => $pipeline->status ? [
                'id' => $pipeline->status->id,
                'title' => $pipeline->status->title,
                'background_colour' => $pipeline->status->background_colour,
                'text_colour' => $pipeline->status->text_colour,
            ] : null,
            'creator' => $pipeline->creator ? ['id' => $pipeline->creator->id, 'name' => $pipeline->creator->name] : null,
            'updater' => $pipeline->updater ? ['id' => $pipeline->updater->id, 'name' => $pipeline->updater->name] : null,
            'deleter' => $pipeline->deleter ? ['id' => $pipeline->deleter->id, 'name' => $pipeline->deleter->name] : null,
            'restorer' => $pipeline->restorer ? ['id' => $pipeline->restorer->id, 'name' => $pipeline->restorer->name] : null,
        ];
    }
}

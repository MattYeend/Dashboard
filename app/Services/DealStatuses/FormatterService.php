<?php

namespace App\Services\DealStatuses;

use App\Models\DealStatus;

class FormatterService
{
    /**
     * Format a single invoice status with all data.
     *
     * @return array<string, mixed>
     */
    public function format(DealStatus $dealStatus): array
    {
        return [
            'id' => $dealStatus->id,
            'title' => $dealStatus->title,
            'description' => $dealStatus->description,
            'background_colour' => $dealStatus->background_colour,
            'text_colour' => $dealStatus->text_colour,
            'meta' => $dealStatus->meta,
            'created_at' => $dealStatus->created_at,
            'updated_at' => $dealStatus->updated_at,
            'deleted_at' => $dealStatus->deleted_at,
            'restored_at' => $dealStatus->restored_at,
            'created_by' => $dealStatus->created_by,
            'updated_by' => $dealStatus->updated_by,
            'deleted_by' => $dealStatus->deleted_by,
            'restored_by' => $dealStatus->restored_by,
            'creator' => $dealStatus->creator ? ['id' => $dealStatus->creator->id, 'name' => $dealStatus->creator->name] : null,
            'updater' => $dealStatus->updater ? ['id' => $dealStatus->updater->id, 'name' => $dealStatus->updater->name] : null,
            'deleter' => $dealStatus->deleter ? ['id' => $dealStatus->deleter->id, 'name' => $dealStatus->deleter->name] : null,
            'restorer' => $dealStatus->restorer ? ['id' => $dealStatus->restorer->id, 'name' => $dealStatus->restorer->name] : null,
        ];
    }
}

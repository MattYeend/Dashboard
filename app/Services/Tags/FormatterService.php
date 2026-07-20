<?php

namespace App\Services\Tags;

use App\Models\Tag;

class FormatterService
{
    /**
     * Format a single tag with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Tag $tag): array
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
            'meta' => $tag->meta,
            'created_at' => $tag->created_at,
            'updated_at' => $tag->updated_at,
            'deleted_at' => $tag->deleted_at,
            'restored_at' => $tag->restored_at,
            'created_by' => $tag->created_by,
            'updated_by' => $tag->updated_by,
            'deleted_by' => $tag->deleted_by,
            'restored_by' => $tag->restored_by,
            'creator' => $tag->creator ? ['id' => $tag->creator->id, 'name' => $tag->creator->name] : null,
            'updater' => $tag->updater ? ['id' => $tag->updater->id, 'name' => $tag->updater->name] : null,
            'deleter' => $tag->deleter ? ['id' => $tag->deleter->id, 'name' => $tag->deleter->name] : null,
            'restorer' => $tag->restorer ? ['id' => $tag->restorer->id, 'name' => $tag->restorer->name] : null,
        ];
    }
}

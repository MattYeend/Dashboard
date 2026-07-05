<?php

namespace App\Services\Industries;

use App\Models\Industry;

class FormatterService
{
    /**
     * Format a single industry with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Industry $industry): array
    {
        return [
            'id' => $industry->id,
            'title' => $industry->title,
            'code' => $industry->code,
            'description' => $industry->description,
            'meta' => $industry->meta,
            'created_at' => $industry->created_at,
            'updated_at' => $industry->updated_at,
            'deleted_at' => $industry->deleted_at,
            'restored_at' => $industry->restored_at,
            'creator' => $industry->creator ? ['id' => $industry->creator->id, 'name' => $industry->creator->name] : null,
            'updater' => $industry->updater ? ['id' => $industry->updater->id, 'name' => $industry->updater->name] : null,
            'deleter' => $industry->deleter ? ['id' => $industry->deleter->id, 'name' => $industry->deleter->name] : null,
            'restorer' => $industry->restorer ? ['id' => $industry->restorer->id, 'name' => $industry->restorer->name] : null,
        ];
    }
}

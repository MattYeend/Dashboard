<?php

namespace App\Services\Labels;

use App\Models\Label;

class FormatterService
{
    /**
     * Format a single label with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Label $label): array
    {
        return [
            'id' => $label->id,
            'name' => $label->name,
            'slug' => $label->slug,
            'background_colour' => $label->background_colour,
            'text_colour' => $label->text_colour,
            'meta' => $label->meta,
            'created_at' => $label->created_at,
            'updated_at' => $label->updated_at,
            'deleted_at' => $label->deleted_at,
            'restored_at' => $label->restored_at,
            'creator' => $label->creator ? ['id' => $label->creator->id, 'name' => $label->creator->name] : null,
            'updater' => $label->updater ? ['id' => $label->updater->id, 'name' => $label->updater->name] : null,
            'deleter' => $label->deleter ? ['id' => $label->deleter->id, 'name' => $label->deleter->name] : null,
            'restorer' => $label->restorer ? ['id' => $label->restorer->id, 'name' => $label->restorer->name] : null,
        ];
    }
}

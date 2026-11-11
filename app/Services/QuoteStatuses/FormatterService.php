<?php

declare(strict_types=1);

namespace App\Services\QuoteStatuses;

use App\Models\QuoteStatus;

class FormatterService
{
    /**
     * Format a single quote status with all data.
     *
     * @return array<string, mixed>
     */
    public function format(QuoteStatus $quoteStatus): array
    {
        return [
            'id' => $quoteStatus->id,
            'title' => $quoteStatus->title,
            'description' => $quoteStatus->description,
            'background_colour' => $quoteStatus->background_colour,
            'text_colour' => $quoteStatus->text_colour,
            'system_key' => $quoteStatus->system_key,
            'is_system' => $quoteStatus->isSystem(),
            'meta' => $quoteStatus->meta,
            'created_at' => $quoteStatus->created_at,
            'updated_at' => $quoteStatus->updated_at,
            'deleted_at' => $quoteStatus->deleted_at,
            'restored_at' => $quoteStatus->restored_at,
            'creator' => $quoteStatus->creator ? ['id' => $quoteStatus->creator->id, 'name' => $quoteStatus->creator->name] : null,
            'updater' => $quoteStatus->updater ? ['id' => $quoteStatus->updater->id, 'name' => $quoteStatus->updater->name] : null,
            'deleter' => $quoteStatus->deleter ? ['id' => $quoteStatus->deleter->id, 'name' => $quoteStatus->deleter->name] : null,
            'restorer' => $quoteStatus->restorer ? ['id' => $quoteStatus->restorer->id, 'name' => $quoteStatus->restorer->name] : null,
        ];
    }
}

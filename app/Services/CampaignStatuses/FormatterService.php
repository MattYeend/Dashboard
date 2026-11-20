<?php

declare(strict_types=1);

namespace App\Services\CampaignStatuses;

use App\Models\CampaignStatus;

class FormatterService
{
    /**
     * Format a single campaign status with all data.
     *
     * @return array<string, mixed>
     */
    public function format(CampaignStatus $campaignStatus): array
    {
        return [
            'id' => $campaignStatus->id,
            'title' => $campaignStatus->title,
            'description' => $campaignStatus->description,
            'background_colour' => $campaignStatus->background_colour,
            'text_colour' => $campaignStatus->text_colour,
            'system_key' => $campaignStatus->system_key,
            'is_system' => $campaignStatus->isSystem(),
            'meta' => $campaignStatus->meta,
            'created_at' => $campaignStatus->created_at,
            'updated_at' => $campaignStatus->updated_at,
            'deleted_at' => $campaignStatus->deleted_at,
            'restored_at' => $campaignStatus->restored_at,
            'created_by' => $campaignStatus->created_by,
            'updated_by' => $campaignStatus->updated_by,
            'deleted_by' => $campaignStatus->deleted_by,
            'restored_by' => $campaignStatus->restored_by,
            'creator' => $campaignStatus->creator ? ['id' => $campaignStatus->creator->id, 'name' => $campaignStatus->creator->name] : null,
            'updater' => $campaignStatus->updater ? ['id' => $campaignStatus->updater->id, 'name' => $campaignStatus->updater->name] : null,
            'deleter' => $campaignStatus->deleter ? ['id' => $campaignStatus->deleter->id, 'name' => $campaignStatus->deleter->name] : null,
            'restorer' => $campaignStatus->restorer ? ['id' => $campaignStatus->restorer->id, 'name' => $campaignStatus->restorer->name] : null,
        ];
    }
}

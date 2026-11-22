<?php

declare(strict_types=1);

namespace App\Services\CampaignSegments;

use App\Models\CampaignSegment;

class FormatterService
{
    /**
     * Format a single campaign segment with all data.
     *
     * @return array<string, mixed>
     */
    public function format(CampaignSegment $campaignSegment): array
    {
        return [
            'id' => $campaignSegment->id,
            'organisation_id' => $campaignSegment->organisation_id,
            'name' => $campaignSegment->name,
            'description' => $campaignSegment->description,
            'criteria' => $campaignSegment->criteria,
            'estimated_recipients_count' => $campaignSegment->estimated_recipients_count,
            'last_refreshed_at' => $campaignSegment->last_refreshed_at?->format('Y-m-d H:i:s'),
            'meta' => $campaignSegment->meta,
            'created_at' => $campaignSegment->created_at,
            'updated_at' => $campaignSegment->updated_at,
            'deleted_at' => $campaignSegment->deleted_at,
            'restored_at' => $campaignSegment->restored_at,
            'creator' => $campaignSegment->creator ? ['id' => $campaignSegment->creator->id, 'name' => $campaignSegment->creator->name] : null,
            'updater' => $campaignSegment->updater ? ['id' => $campaignSegment->updater->id, 'name' => $campaignSegment->updater->name] : null,
            'deleter' => $campaignSegment->deleter ? ['id' => $campaignSegment->deleter->id, 'name' => $campaignSegment->deleter->name] : null,
            'restorer' => $campaignSegment->restorer ? ['id' => $campaignSegment->restorer->id, 'name' => $campaignSegment->restorer->name] : null,
        ];
    }
}

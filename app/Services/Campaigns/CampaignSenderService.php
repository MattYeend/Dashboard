<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\CampaignStatus;

class CampaignSenderService
{
    public function isSendable(Campaign $campaign): bool
    {
        return $campaign->status_id === CampaignStatus::idForKey(CampaignStatus::KEY_SENDING);
    }

    /**
     * Recount from the source of truth and finish the campaign when nothing is pending.
     */
    public function refreshCounts(Campaign $campaign): void
    {
        $counts = CampaignRecipient::where('campaign_id', $campaign->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pending = (int) ($counts['pending'] ?? 0);

        $attributes = [
            'sent_count' => (int) ($counts['sent'] ?? 0),
            'failed_count' => (int) ($counts['failed'] ?? 0),
            'skipped_count' => (int) ($counts['skipped'] ?? 0),
            'unsubscribed_count' => (int) ($counts['unsubscribed'] ?? 0),
        ];

        if ($pending === 0 && $this->isSendable($campaign)) {
            $attributes['status_id'] = CampaignStatus::idForKey(CampaignStatus::KEY_SENT);
            $attributes['completed_at'] = now();
        }

        $campaign->forceFill($attributes)->save();
    }
}

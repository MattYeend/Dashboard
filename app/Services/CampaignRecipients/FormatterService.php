<?php

declare(strict_types=1);

namespace App\Services\CampaignRecipients;

use App\Models\CampaignRecipient;

class FormatterService
{
    /**
     * Shape a campaign recipient for the frontend.
     *
     * @return array<string, mixed>
     */
    public function format(CampaignRecipient $campaignRecipient): array
    {
        return [
            'id' => $campaignRecipient->id,
            'campaign_id' => $campaignRecipient->campaign_id,
            'contact_id' => $campaignRecipient->contact_id,
            'email' => $campaignRecipient->email,
            'name' => $campaignRecipient->name,
            'status' => $campaignRecipient->status,
            'attempts' => $campaignRecipient->attempts,
            'sent_at' => $campaignRecipient->sent_at?->format('Y-m-d\TH:i'),
            'opened_at' => $campaignRecipient->opened_at?->format('Y-m-d\TH:i'),
            'clicked_at' => $campaignRecipient->clicked_at?->format('Y-m-d\TH:i'),
            'unsubscribed_at' => $campaignRecipient->unsubscribed_at?->format('Y-m-d\TH:i'),
            'failed_at' => $campaignRecipient->failed_at?->format('Y-m-d\TH:i'),
            'failure_reason' => $campaignRecipient->failure_reason,
            'meta' => $campaignRecipient->meta,
            'created_at' => $campaignRecipient->created_at,
            'updated_at' => $campaignRecipient->updated_at,
            'deleted_at' => $campaignRecipient->deleted_at,
            'restored_at' => $campaignRecipient->restored_at,
            'created_by' => $campaignRecipient->created_by,
            'updated_by' => $campaignRecipient->updated_by,
            'deleted_by' => $campaignRecipient->deleted_by,
            'restored_by' => $campaignRecipient->restored_by,
            'campaign' => $campaignRecipient->campaign ? ['id' => $campaignRecipient->campaign->id, 'name' => $campaignRecipient->campaign->name] : null,
            'contact' => $campaignRecipient->contact ? ['id' => $campaignRecipient->contact->id, 'title' => $campaignRecipient->contact->title] : null,
            'creator' => $campaignRecipient->creator ? ['id' => $campaignRecipient->creator->id, 'name' => $campaignRecipient->creator->name] : null,
            'updater' => $campaignRecipient->updater ? ['id' => $campaignRecipient->updater->id, 'name' => $campaignRecipient->updater->name] : null,
            'deleter' => $campaignRecipient->deleter ? ['id' => $campaignRecipient->deleter->id, 'name' => $campaignRecipient->deleter->name] : null,
            'restorer' => $campaignRecipient->restorer ? ['id' => $campaignRecipient->restorer->id, 'name' => $campaignRecipient->restorer->name] : null,
        ];
    }
}

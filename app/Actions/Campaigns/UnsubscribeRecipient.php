<?php

declare(strict_types=1);

namespace App\Actions\Campaigns;

use App\Models\CampaignRecipient;
use App\Services\Campaigns\CampaignSenderService;
use App\Services\Campaigns\SuppressionService;
use Illuminate\Support\Facades\DB;

class UnsubscribeRecipient
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly SuppressionService $suppression,
        private readonly CampaignSenderService $sender,
    ) {}

    /**
     * Suppress the recipient's address and mark the recipient unsubscribed.
     *
     * Must be called inside the recipient's organisation, so scoped models
     * and audit logging resolve the correct tenant. Safe to call repeatedly.
     */
    public function execute(CampaignRecipient $recipient): void
    {
        DB::transaction(function () use ($recipient): void {
            $this->suppression->suppress(
                $recipient->email,
                'unsubscribed',
                $recipient->campaign_id,
                $recipient->contact_id,
            );

            // Lock the row so two simultaneous requests cannot both refresh the counts.
            $locked = CampaignRecipient::query()
                ->lockForUpdate()
                ->find($recipient->getKey());

            if ($locked === null || $locked->unsubscribed_at !== null) {
                return;
            }

            $locked->forceFill([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ])->save();

            $this->sender->refreshCounts($locked->campaign);
        });
    }
}

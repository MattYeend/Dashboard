<?php

declare(strict_types=1);

namespace App\Actions\Campaigns;

use App\Models\Campaign;
use App\Services\Campaigns\CampaignTransitionService;

class CancelCampaign
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly CampaignTransitionService $transitions) {}

    /**
     * Cancel a draft, scheduled or paused campaign and skip its pending recipients.
     */
    public function execute(Campaign $campaign): Campaign
    {
        return $this->transitions->cancel($campaign);
    }
}

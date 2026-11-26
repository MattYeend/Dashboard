<?php

declare(strict_types=1);

namespace App\Actions\Campaigns;

use App\Models\Campaign;
use App\Services\Campaigns\CampaignTransitionService;
use Carbon\CarbonInterface;

class ScheduleCampaign
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly CampaignTransitionService $transitions)
    {
    }

    /**
     * Move a draft campaign to Scheduled for the given send time.
     */
    public function execute(Campaign $campaign, CarbonInterface $scheduledAt): Campaign
    {
        return $this->transitions->schedule($campaign, $scheduledAt);
    }
}
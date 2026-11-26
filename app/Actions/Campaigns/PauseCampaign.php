<?php

declare(strict_types=1);

namespace App\Actions\Campaigns;

use App\Models\Campaign;
use App\Services\Campaigns\CampaignTransitionService;

class PauseCampaign
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly CampaignTransitionService $transitions) {}

    /**
     * Move a sending campaign to Paused so queued jobs stop sending.
     */
    public function execute(Campaign $campaign): Campaign
    {
        return $this->transitions->pause($campaign);
    }
}

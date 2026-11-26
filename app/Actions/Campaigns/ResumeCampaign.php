<?php

declare(strict_types=1);

namespace App\Actions\Campaigns;

use App\Models\Campaign;
use App\Services\Campaigns\CampaignTransitionService;

class ResumeCampaign
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly CampaignTransitionService $transitions)
    {
    }

    /**
     * Move a paused campaign back to Sending and queue its pending recipients.
     */
    public function execute(Campaign $campaign): Campaign
    {
        return $this->transitions->resume($campaign);
    }
}
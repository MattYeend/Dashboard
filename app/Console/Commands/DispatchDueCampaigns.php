<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\StartCampaignJob;
use App\Models\Campaign;
use App\Models\CampaignStatus;
use App\Models\Organisation;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('campaigns:dispatch-due')]
#[Description('Start scheduled campaigns whose send time has arrived')]
class DispatchDueCampaigns extends Command
{
    /**
     * Execute the console command.
     *
     * Scopes are removed to find due campaigns across every organisation.
     * Each job is then dispatched while its own organisation is current, so
     * the tenant aware queue stores the right tenant in the job payload and
     * one organisation can never send to another organisation's contacts.
     */
    public function handle(): int
    {
        // Every organisation has its own Scheduled status row, so match any of them.
        $scheduledStatusIds = CampaignStatus::withoutGlobalScopes()
            ->where('system_key', CampaignStatus::KEY_SCHEDULED)
            ->select('id');

        $dispatched = 0;

        Campaign::withoutGlobalScopes()
            ->whereIn('status_id', $scheduledStatusIds)
            ->where('scheduled_at', '<=', now())
            ->chunkById(100, function ($campaigns) use (&$dispatched): void {
                foreach ($campaigns as $campaign) {
                    $organisation = Organisation::withoutGlobalScopes()->find($campaign->organisation_id);

                    if ($organisation === null) {
                        continue;
                    }

                    $organisation->makeCurrent();

                    try {
                        StartCampaignJob::dispatch($campaign->id);
                        $dispatched++;
                    } finally {
                        Organisation::forgetCurrent();
                    }
                }
            });

        $this->info("Dispatched {$dispatched} campaign(s).");

        return self::SUCCESS;
    }
}

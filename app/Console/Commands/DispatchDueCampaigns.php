<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\StartCampaignJob;
use App\Models\Campaign;
use App\Models\CampaignStatus;
use Illuminate\Console\Command;

class DispatchDueCampaigns extends Command
{
    protected $signature = 'campaigns:dispatch-due';

    protected $description = 'Start scheduled campaigns whose send time has arrived';

    public function handle(): int
    {
        // withoutGlobalScopes is acceptable here only: the job re-enters the tenant via the queue payload.
        // Add Campaign to config/organisations.php global_scope_bypass_allow_list for this command.
        Campaign::withoutGlobalScopes()
            ->where('status_id', CampaignStatus::withoutGlobalScopes()->where('system_key', CampaignStatus::KEY_SCHEDULED)->select('id'))
            ->where('scheduled_at', '<=', now())
            ->each(fn (Campaign $c) => StartCampaignJob::dispatch($c->id));

        return self::SUCCESS;
    }
}
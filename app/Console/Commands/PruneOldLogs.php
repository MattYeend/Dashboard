<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Services\Log\DeleterService;

#[Signature('app:prune-old-logs')]
#[Description('Command description')]
class PruneOldLogs extends Command
{
    public function __construct(private readonly DeleterService $deleterService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');

        if ($days < 1) {
            $this->error('The --days option must be a positive integer.');

            return self::FAILURE;
        }

        $deleted = $this->deleterService->deleteOlderThan($days);

        $this->info("Deleted {$deleted} log record(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Services\Log\DeleterService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('logs:prune {--days=30 : Number of days to retain logs}')]
#[Description('Delete log records older than the specified number of days (default 30).')]
class PruneOldLogs extends Command
{
    public function __construct(private readonly DeleterService $deleterService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
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

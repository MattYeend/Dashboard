<?php

namespace App\Console\Commands;

use App\Services\Logs\IntegrityService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('logs:seal')]
#[Description('Seal new audit log rows into the tamper-evident hash chain.')]
class SealAuditLogs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(IntegrityService $integrityService): int
    {
        $sealed = $integrityService->seal();

        $this->info("Sealed {$sealed} audit log rows.");

        return self::SUCCESS;
    }
}

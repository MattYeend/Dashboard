<?php

namespace App\Console\Commands;

use App\Services\Logs\IntegrityService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('logs:verify {--from= : Start verifying at this sequence number}')]
#[Description('Verify the audit log hash chain and report any tampering.')]
class VerifyAuditLogs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(IntegrityService $integrityService): int
    {
        $from = $this->option('from') !== null ? (int) $this->option('from') : null;
        $failures = $integrityService->verify($from);
        $stale = $integrityService->unsealedOlderThan(15);

        if ($stale > 0) {
            $this->warn("{$stale} audit rows are older than 15 minutes and still unsealed. Is logs:seal running?");
        }

        if ($failures === []) {
            $this->info('Audit log integrity verified.');

            return self::SUCCESS;
        }

        foreach ($failures as $failure) {
            $this->error("Sequence {$failure['sequence']}: {$failure['reason']}");
        }

        logger()->critical('Audit log integrity failure', ['failures' => $failures]);

        return self::FAILURE;
    }
}

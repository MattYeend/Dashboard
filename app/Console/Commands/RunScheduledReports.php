<?php

namespace App\Console\Commands;

use App\Models\Log;
use App\Models\Report;
use App\Services\AuditLogService;
use App\Services\Reports\DataPreparationService;
use App\Services\Reports\RunnerService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reports:run-scheduled')]
#[Description('Generates and distributes reports that are due to run')]
class RunScheduledReports extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(
        RunnerService $runnerService,
        DataPreparationService $dataPreparationService,
        AuditLogService $auditLogService
    ): int {
        $due = Report::query()
            ->where('is_scheduled', true)
            ->where('next_run_at', '<=', now())
            ->get();

        foreach ($due as $report) {
            try {
                $runnerService->run($report, $report->creator);

                $report->forceFill([
                    'next_run_at' => $dataPreparationService->resolveNextRunAt(
                        $report->schedule_frequency,
                        $report->schedule_time
                    ),
                ])->save();

                $auditLogService->record(
                    Log::ACTION_REPORT_UPDATED_BY_CRON,
                    $report->creator,
                    $report,
                    [
                        'after' => [
                            'next_run_at' => $report->next_run_at,
                        ],
                    ],
                );
            } catch (\Throwable $e) {
                $this->error(
                    "Scheduled report {$report->id} failed: {$e->getMessage()}"
                );
            }
        }

        $this->info(
            "Processed {$due->count()} scheduled report(s)."
        );

        return self::SUCCESS;
    }
}

<?php

namespace App\Services\Reports;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\Report;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted report.
     *
     * @throws \Exception
     */
    public function restore(
        Report $report,
        int $restoredBy,
        ?User $actor = null
    ): Report {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $report,
            function (Report $report) use ($actor, $restoredBy): void {
                $report->restored_by = $restoredBy;
                $report->restored_at = now();
                $report->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_REPORT,
                    $actor,
                    $report,
                    ['before' => $this->auditLogService->snapshot($report)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted reports.
     *
     * @return int Number of reports restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(array $reportIds, int $restoredBy): int
    {
        $count = 0;

        DB::transaction(function () use ($reportIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, Report> $reports */
            $reports = Report::withTrashed()
                ->whereIn('id', $reportIds)
                ->get();

            foreach ($reports as $report) {
                if ($report->trashed()) {
                    $this->restore($report, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}

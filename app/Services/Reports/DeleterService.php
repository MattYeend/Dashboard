<?php

namespace App\Services\Reports;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\Report;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete a report.
     *
     * @throws \Exception
     */
    public function delete(
        Report $report,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $report,
            function (Report $report) use ($actor, $deletedBy): void {
                $report->deleted_by = $deletedBy;
                $report->deleted_at = now();
                $report->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_REPORT,
                    $actor,
                    $report,
                    ['before' => $this->auditLogService->snapshot($report)],
                );
            });
    }

    /**
     * Force delete a report (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(Report $report, int $deletedBy): bool
    {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $report,
            function (Report $report) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_REPORT,
                    $actor,
                    $report,
                    ['before' => $this->auditLogService->snapshot($report)],
                );
            });
    }

    /**
     * Delete multiple reports.
     *
     * @throws \Exception
     */
    public function deleteMultiple(array $reportIds, int $deletedBy): int
    {
        $count = 0;

        DB::transaction(function () use ($reportIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $reports = Report::whereIn('id', $reportIds)->get();

            foreach ($reports as $report) {
                if ($this->delete($report, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

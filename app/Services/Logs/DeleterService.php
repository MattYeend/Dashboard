<?php

namespace App\Services\Logs;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Carbon;
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
     * Delete log records older than the given number of days.
     *
     * Deletes in batches to avoid loading the whole result set into memory
     * or holding a long-running transaction on a potentially large table.
     */
    public function deleteOlderThan(
        int $days = 30
    ): int {
        $cutoff = Carbon::now()->subDays(
            $days
        );
        $totalDeleted = 0;

        do {
            $deletedCount = Log::query()
                ->where(
                    'created_at',
                    '<',
                    $cutoff
                )
                ->limit(500)
                ->delete();

            $totalDeleted += $deletedCount;
        } while ($deletedCount > 0);

        return $totalDeleted;
    }

    /**
     * Permanently delete an activity log entry.
     *
     * Activity logs are not soft-deletable, so this always uses
     * forceHandle() rather than the soft-delete handle() used elsewhere.
     *
     * @throws \Exception
     */
    public function delete(Log $log, int $deletedBy, ?User $actor = null): bool
    {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $log,
            function (Log $log) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_DELETE_ACTIVITY_LOG,
                    $actor,
                    $log,
                    ['before' => $this->auditLogService->snapshot($log)],
                );
            });
    }

    /**
     * Delete multiple activity log entries.
     *
     * @throws \Exception
     */
    public function deleteMultiple(array $logIds, int $deletedBy): int
    {
        $count = 0;

        DB::transaction(function () use ($logIds, $deletedBy, &$count) {
            $logs = Log::whereIn('id', $logIds)->get();

            foreach ($logs as $log) {
                if ($this->delete($log, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

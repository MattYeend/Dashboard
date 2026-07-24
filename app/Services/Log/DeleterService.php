<?php

namespace App\Services\Log;

use App\Models\Log;
use Illuminate\Support\Carbon;

class DeleterService
{
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
}

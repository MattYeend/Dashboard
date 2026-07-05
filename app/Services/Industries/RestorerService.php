<?php

namespace App\Services\Industries;

use App\Actions\RestoreResource;
use App\Models\Industry;
use App\Models\Log;
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
     * Restore a soft-deleted industry.
     *
     * @throws \Exception
     */
    public function restore(Industry $industry, int $restoredBy): Industry
    {
        $actor = User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $industry,
            function (Industry $industry) use ($actor, $restoredBy): void {
                $industry->restored_by = $restoredBy;
                $industry->restored_at = now();
                $industry->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_INDUSTRY,
                    $actor,
                    $industry,
                    ['before' => $industry->toArray()],
                );
            });
    }

    /**
     * Restore multiple soft-deleted industries.
     *
     * @return int Number of industries restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(array $industryIds, int $restoredBy): int
    {
        $count = 0;

        DB::transaction(function () use ($industryIds, $restoredBy, &$count) {
            /** @var Collection<int, Industry> $industries */
            $industries = Industry::withTrashed()
                ->whereIn('id', $industryIds)
                ->get();

            foreach ($industries as $industry) {
                if ($industry->trashed()) {
                    $this->restore($industry, $restoredBy);
                    $count++;
                }
            }
        });

        return $count;
    }
}

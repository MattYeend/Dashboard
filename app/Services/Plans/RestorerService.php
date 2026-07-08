<?php

namespace App\Services\Plans;

use App\Actions\RestoreResource;
use App\Models\Plan;
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
     * Restore a soft-deleted plan.
     *
     * @throws \Exception
     */
    public function restore(Plan $plan, int $restoredBy): Plan
    {
        $actor = User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $plan,
            function (Plan $plan) use ($actor, $restoredBy): void {
                $plan->restored_by = $restoredBy;
                $plan->restored_at = now();
                $plan->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_PLAN,
                    $actor,
                    $plan,
                    ['before' => $plan->toArray()],
                );
            });
    }

    /**
     * Restore multiple soft-deleted plans.
     *
     * @return int Number of plans restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(array $planIds, int $restoredBy): int
    {
        $count = 0;

        DB::transaction(function () use ($planIds, $restoredBy, &$count) {
            /** @var Collection<int, Plan> $plans */
            $plans = Plan::withTrashed()
                ->whereIn('id', $planIds)
                ->get();

            foreach ($plans as $plan) {
                if ($plan->trashed()) {
                    $this->restore($plan, $restoredBy);
                    $count++;
                }
            }
        });

        return $count;
    }
}

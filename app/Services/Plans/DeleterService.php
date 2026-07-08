<?php

namespace App\Services\Plans;

use App\Actions\DeleteResource;
use App\Models\Plan;
use App\Models\Log;
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
     * Soft delete an plan.
     *
     * @throws \Exception
     */
    public function delete(Plan $plan, int $deletedBy): bool
    {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $plan,
            function (Plan $plan) use ($actor, $deletedBy): void {
                $plan->deleted_by = $deletedBy;
                $plan->deleted_at = now();
                $plan->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_PLAN,
                    $actor,
                    $plan,
                    ['before' => $plan->toArray()],
                );
            });
    }

    /**
     * Force delete an plan (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(Plan $plan, int $deletedBy): bool
    {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $plan,
            function (Plan $plan) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_PLAN,
                    $actor,
                    $plan,
                    ['before' => $plan->toArray()],
                );
            });
    }

    /**
     * Delete multiple plans.
     *
     * @throws \Exception
     */
    public function deleteMultiple(array $planIds, int $deletedBy): int
    {
        $count = 0;

        DB::transaction(function () use ($planIds, $deletedBy, &$count) {
            $plans = Plan::whereIn('id', $planIds)->get();

            foreach ($plans as $plan) {
                if ($this->delete($plan, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

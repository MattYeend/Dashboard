<?php

namespace App\Services\Plans;

use App\Http\Requests\Plans\StorePlanRequest;
use App\Http\Requests\Plans\UpdatePlanRequest;
use App\Models\Plan;
use App\Models\User;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $destructor,
        protected readonly RestorerService $restorer
    ) {}

    /**
     * Create a new plan.
     */
    public function store(StorePlanRequest $request): Plan
    {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing plan.
     */
    public function update(
        UpdatePlanRequest $request,
        Plan $plan
    ): Plan {
        return $this->updater->update(
            $plan,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a plan.
     */
    public function destroy(
        Plan $plan,
        User $actor
    ): void {
        $this->destructor->delete($plan, $actor->id);
    }

    /**
     * Restore a soft-deleted plan.
     */
    public function restore(
        int $id,
        User $actor
    ): Plan {
        $plan = Plan::withTrashed()->findOrFail($id);

        return $this->restorer->restore($plan, $actor->id);
    }

    /**
     * Force delete a plan, permanently removing it from the database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $plan = Plan::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($plan, $actor->id);
    }

    /**
     * Bulk restore plans.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $plans = Plan::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($plans as $plan) {
            /** @var Plan $plan */
            $authoriseCallback($plan);
            $this->restorer->restore($plan, $actor->id);
            $restored[] = $plan->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($plans->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete plans.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $deleted = [];

        foreach ($ids as $id) {
            $plan = Plan::findOrFail($id);
            $authoriseCallback($plan);

            $this->destructor->delete($plan, $actor->id);
            $deleted[] = $id;
        }

        return $deleted;
    }
}

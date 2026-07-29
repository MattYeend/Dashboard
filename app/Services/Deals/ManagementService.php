<?php

namespace App\Services\Deals;

use App\Http\Requests\Deals\StoreDealRequest;
use App\Http\Requests\Deals\UpdateDealRequest;
use App\Models\Deal;
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
        protected readonly RestorerService $restorer,
    ) {}

    /**
     * Create a new deal.
     */
    public function store(
        StoreDealRequest $request
    ): Deal {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing deal.
     */
    public function update(
        UpdateDealRequest $request,
        Deal $deal
    ): Deal {
        return $this->updater->update(
            $deal,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a deal.
     */
    public function destroy(
        Deal $deal,
        User $actor
    ): void {
        $this->destructor->delete($deal, $actor->id);
    }

    /**
     * Restore a soft-deleted deal.
     */
    public function restore(
        int $id,
        User $actor
    ): Deal {
        $deal = Deal::withTrashed()->findOrFail($id);

        return $this->restorer->restore($deal, $actor->id);
    }

    /**
     * Force delete a deal, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $deal = Deal::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($deal, $actor->id);
    }

    /**
     * Bulk restore deals.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $deals = Deal::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($deals as $deal) {
            /** @var Deal $deal */
            $authoriseCallback($deal);
            $this->restorer->restore($deal, $actor->id);
            $restored[] = $deal->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($deals->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete deals.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $deals = Deal::whereIn('id', $requestedIds)
            ->get();

        $deleted = [];

        foreach ($deals as $deal) {
            /** @var Deal $deal */
            $authoriseCallback($deal);
            $this->destructor->delete($deal, $actor->id);
            $deleted[] = $deal->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($deals->pluck('id'))
                ->values()
                ->all(),
        ];
    }
}
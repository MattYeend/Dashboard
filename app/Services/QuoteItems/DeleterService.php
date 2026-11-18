<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Quotes\TotalsService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly TotalsService $totals,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete a line and recalculate the quote header.
     *
     * @throws AuthorizationException
     */
    public function delete(
        QuoteItem $item,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return DB::transaction(function () use ($item, $deletedBy, $actor): bool {
            $quote = Quote::query()
                ->with('status')
                ->whereKey($item->quote_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $this->activeChecker->quoteAcceptsChanges($quote) || $item->trashed()) {
                throw new AuthorizationException('Lines can only be removed from a draft quote.');
            }

            return $this->deleteResource->handle(
                $item,
                function (QuoteItem $item) use ($quote, $actor, $deletedBy): void {
                    $item->deleted_by = $deletedBy;
                    $item->deleted_at = now();
                    $item->save();

                    $this->totals->recalculate($quote);

                    $this->auditLogService->record(
                        Log::ACTION_DELETE_QUOTE_ITEM,
                        $actor,
                        $item,
                        ['before' => $this->auditLogService->snapshot($item)],
                    );
                });
        });
    }

    /**
     * Force delete a trashed line (permanent deletion).
     *
     * Totals are unaffected because trashed lines are already excluded.
     *
     * @throws AuthorizationException
     */
    public function forceDelete(
        QuoteItem $item,
        int $deletedBy
    ): bool {
        if (! $this->activeChecker->canBeForceDeleted($item)) {
            throw new AuthorizationException('Only a trashed line can be permanently deleted.');
        }

        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $item,
            function (QuoteItem $item) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_QUOTE_ITEM,
                    $actor,
                    $item,
                    ['before' => $this->auditLogService->snapshot($item)],
                );
            });
    }
}
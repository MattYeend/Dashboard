<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Quotes\TotalsService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly TotalsService $totals,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted line and recalculate the quote header.
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function restore(
        QuoteItem $item,
        int $restoredBy,
        ?User $actor = null
    ): QuoteItem {
        $actor ??= User::findOrFail($restoredBy);

        return DB::transaction(function () use ($item, $restoredBy, $actor): QuoteItem {
            $quote = Quote::query()
                ->with('status')
                ->whereKey($item->quote_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $this->activeChecker->quoteAcceptsChanges($quote) || ! $item->trashed()) {
                throw new AuthorizationException('Lines can only be restored on a draft quote.');
            }

            if (QuoteItem::query()->where('quote_id', $quote->id)->count() >= QuoteItem::MAX_PER_QUOTE) {
                throw ValidationException::withMessages([
                    'quantity' => 'A quote can hold at most '.QuoteItem::MAX_PER_QUOTE.' lines.',
                ]);
            }

            return $this->restoreResource->handle(
                $item,
                function (QuoteItem $item) use ($quote, $actor, $restoredBy): void {
                    $item->forceFill([
                        'restored_by' => $restoredBy,
                        'restored_at' => now(),
                    ])->save();

                    $this->totals->recalculate($quote);

                    $this->auditLogService->record(
                        Log::ACTION_RESTORE_QUOTE_ITEM,
                        $actor,
                        $item,
                        ['before' => $this->auditLogService->snapshot($item)],
                    );
                });
        });
    }
}

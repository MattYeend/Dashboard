<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Actions\Quote\RecalculateQuoteTotal;
use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\QuoteItem;
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
        protected readonly RecalculateQuoteTotal $recalculateQuoteTotal,
    ) {}

    /**
     * Restore a soft-deleted quote item.
     *
     * @throws \Exception
     */
    public function restore(
        QuoteItem $quoteItem,
        int $restoredBy,
        ?User $actor = null
    ): QuoteItem {
        $actor ??= User::findOrFail($restoredBy);

        $quoteItem->loadMissing('quote');

        return $this->restoreResource->handle(
            $quoteItem,
            function (QuoteItem $quoteItem) use ($actor, $restoredBy): void {
                $quoteItem->restored_by = $restoredBy;
                $quoteItem->restored_at = now();
                $quoteItem->save();

                $this->recalculateQuoteTotal->execute(
                    $quoteItem->quote
                );

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_QUOTE_ITEM,
                    $actor,
                    $quoteItem,
                    [
                        'after' => $this->auditLogService->snapshot(
                            $quoteItem
                        ),
                    ],
                );
            }
        );
    }

    /**
     * Restore multiple soft-deleted quote items.
     *
     * @param  array<int, int|string>  $quoteItemIds
     * @return int Number of quote items restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $quoteItemIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($quoteItemIds, $restoredBy, &$count): void {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, QuoteItem> $quoteItems */
            $quoteItems = QuoteItem::onlyTrashed()
                ->whereIn('id', array_map('intval', $quoteItemIds))
                ->get();

            foreach ($quoteItems as $quoteItem) {
                $this->restore($quoteItem, $restoredBy, $actor);
                $count++;
            }
        });

        return $count;
    }
}

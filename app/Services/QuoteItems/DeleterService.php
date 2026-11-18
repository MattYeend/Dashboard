<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Actions\DeleteResource;
use App\Actions\Quote\RecalculateQuoteTotal;
use App\Models\Log;
use App\Models\QuoteItem;
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
        protected readonly RecalculateQuoteTotal $recalculateQuoteTotal,
    ) {}

    /**
     * Soft delete a quote item.
     *
     * @throws \Exception
     */
    public function delete(
        QuoteItem $quoteItem,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        $quoteItem->loadMissing('quote');

        return $this->deleteResource->handle(
            $quoteItem,
            function (QuoteItem $quoteItem) use ($actor, $deletedBy): void {
                $quoteItem->deleted_by = $deletedBy;
                $quoteItem->deleted_at = now();
                $quoteItem->save();

                $this->recalculateQuoteTotal->execute(
                    $quoteItem->quote
                );

                $this->auditLogService->record(
                    Log::ACTION_DELETE_QUOTE_ITEM,
                    $actor,
                    $quoteItem,
                    [
                        'before' => $this->auditLogService->snapshot(
                            $quoteItem
                        ),
                    ],
                );
            }
        );
    }

    /**
     * Force delete a quote item (permanent deletion).
     *
     * Soft deleted lines are already excluded from the totals, so no
     * recalculation is needed here.
     *
     * @throws \Exception
     */
    public function forceDelete(
        QuoteItem $quoteItem,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $quoteItem,
            function (QuoteItem $quoteItem) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_QUOTE_ITEM,
                    $actor,
                    $quoteItem,
                    [
                        'before' => $this->auditLogService->snapshot(
                            $quoteItem
                        ),
                    ],
                );
            }
        );
    }

    /**
     * Delete multiple quote items.
     *
     * @param  array<int, int|string>  $quoteItemIds
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $quoteItemIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($quoteItemIds, $deletedBy, &$count): void {
            $actor = User::findOrFail($deletedBy);

            // Tenant global scope restricts this to the current tenant.
            $quoteItems = QuoteItem::query()
                ->whereIn('id', array_map('intval', $quoteItemIds))
                ->get();

            foreach ($quoteItems as $quoteItem) {
                if ($this->delete($quoteItem, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}

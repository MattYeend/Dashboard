<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Actions\UpdateResource;
use App\Actions\Quote\RecalculateQuoteTotal;
use App\Models\Log;
use App\Models\QuoteItem;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
        protected readonly RecalculateQuoteTotal $recalculateQuoteTotal,
    ) {}

    /**
     * Update an existing quote item.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        QuoteItem $quoteItem,
        array $data,
        int $updatedBy
    ): QuoteItem {
        $actor = User::findOrFail(
            $updatedBy
        );

        $before = $this->auditLogService->snapshot(
            $quoteItem
        );

        $quoteItemData = $this->dataPreparation->prepareForUpdate(
            $data,
            $quoteItem
        );

        return $this->updateResource->handle(
            $quoteItem,
            $quoteItemData,
            function (QuoteItem $quoteItem) use ($actor, $before, $updatedBy): void {
                $quoteItem->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();

                $fresh = $quoteItem->fresh();

                $this->recalculateQuoteTotal->execute(
                    $fresh->quote
                );

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_QUOTE_ITEM,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot(
                            $fresh
                        ),
                    ],
                );
            }
        );
    }
}

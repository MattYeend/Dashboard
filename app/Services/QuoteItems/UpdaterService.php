<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Quotes\TotalsService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly TotalsService $totals,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update a line and recalculate the quote header.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function update(
        QuoteItem $item,
        array $data,
        int $updatedBy
    ): QuoteItem {
        $actor = User::findOrFail($updatedBy);

        return DB::transaction(function () use ($item, $data, $updatedBy, $actor): QuoteItem {
            $quote = Quote::query()
                ->with('status')
                ->whereKey($item->quote_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $this->activeChecker->quoteAcceptsChanges($quote) || $item->trashed()) {
                throw new AuthorizationException('Lines can only be changed on a draft quote.');
            }

            $before = $this->auditLogService->snapshot($item);

            $itemData = $this->dataPreparation->prepareForUpdate($data, $item);

            return $this->updateResource->handle(
                $item,
                $itemData,
                function (QuoteItem $item) use ($quote, $actor, $before, $updatedBy): void {
                    $item->forceFill(['updated_by' => $updatedBy])->save();

                    $this->totals->recalculate($quote);

                    $fresh = $item->fresh();

                    $this->auditLogService->record(
                        Log::ACTION_UPDATE_QUOTE_ITEM,
                        $actor,
                        $fresh,
                        [
                            'before' => $before,
                            'after' => $this->auditLogService->snapshot($fresh),
                        ],
                    );
                });
        });
    }
}

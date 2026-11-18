<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Actions\CreateResource;
use App\Actions\Quotes\RecalculateQuoteTotal;
use App\Models\Log;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
        protected readonly RecalculateQuoteTotal $recalculateQuoteTotal,
    ) {}

    /**
     * Create a new quote item.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(
        array $data,
        int $quoteId,
        int $createdBy
    ): QuoteItem {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($quoteId, $createdBy, $actor): QuoteItem {
                $quote = Quote::query()->lockForUpdate()->findOrFail($quoteId);

                $position = isset($data['position'])
                    ? (int) $data['position']
                    : (int) QuoteItem::withTrashed()
                        ->where('quote_id', $quote->id)
                        ->max('position') + 1;

                // Built entirely server side by prepareForCreation, so
                // forceFill is safe and gives a single INSERT.
                $newQuoteItem = (new QuoteItem)->forceFill([
                    ...$this->dataPreparation->prepareForCreation($data, $quote, $position),
                    'created_by' => $createdBy,
                ]);
                $newQuoteItem->save();

                $this->recalculateQuoteTotal->execute($quote);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_QUOTE_ITEM,
                    $actor,
                    $newQuoteItem,
                    [
                        'after' => $this->auditLogService->snapshot(
                            $newQuoteItem
                        ),
                    ],
                );

                return $newQuoteItem;
            }
        );
    }
}

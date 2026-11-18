<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Quotes\TotalsService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly TotalsService $totals,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Add a line to a quote.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function create(Quote $quote, array $data, int $createdBy): QuoteItem
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($quote, $createdBy, $actor): QuoteItem {
                return DB::transaction(function () use ($quote, $data, $createdBy, $actor): QuoteItem {
                    $locked = Quote::query()
                        ->with('status')
                        ->whereKey($quote->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    // Second layer: Super Admin bypasses the policy through Gate::before.
                    if (! $this->activeChecker->quoteAcceptsChanges($locked)) {
                        throw new AuthorizationException('Lines can only be added to a draft quote.');
                    }

                    $existing = QuoteItem::query()->where('quote_id', $locked->id)->count();

                    if ($existing >= QuoteItem::MAX_PER_QUOTE) {
                        throw ValidationException::withMessages([
                            'quantity' => 'A quote can hold at most '.QuoteItem::MAX_PER_QUOTE.' lines.',
                        ]);
                    }

                    $position = (int) QuoteItem::withTrashed()
                        ->where('quote_id', $locked->id)
                        ->max('position') + 1;

                    $item = QuoteItem::create(
                        $this->dataPreparation->prepareForCreation($data, $locked, $position)
                    );

                    $item->forceFill(['created_by' => $createdBy])->save();

                    $this->totals->recalculate($locked);

                    $this->auditLogService->record(
                        Log::ACTION_CREATE_QUOTE_ITEM,
                        $actor,
                        $item,
                        ['after' => $this->auditLogService->snapshot($item)],
                    );

                    return $item;
                });
            });
    }
}
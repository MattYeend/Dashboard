<?php

declare(strict_types=1);

namespace App\Actions\Quotes;

use App\Models\Log;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Quote;
use App\Models\QuoteStatus;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Orders\CreatorService as OrderCreatorService;
use App\Services\Orders\OrderableTypeRegistryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConvertQuoteToOrder
{
    /**
     * Inject the required services into the action.
     */
    public function __construct(
        private readonly OrderCreatorService $orderCreator,
        private readonly OrderableTypeRegistryService $orderableRegistry,
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Convert an accepted quote into an order.
     *
     * Runs in one transaction with a row lock on the quote, so converting
     * twice (a double click, or two users) produces exactly one order.
     *
     * @throws ValidationException
     */
    public function handle(Quote $quote, User $actor): Order
    {
        return DB::transaction(function () use ($quote, $actor): Order {
            $locked = Quote::query()
                ->with('status')
                ->whereKey($quote->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->isStatus(QuoteStatus::KEY_ACCEPTED) || $locked->converted_order_id !== null) {
                throw ValidationException::withMessages([
                    'status' => 'Only an accepted quote that has not been converted can be converted.',
                ]);
            }

            $orderableKey = $this->orderableRegistry->keyForModel($locked->quotable_type);

            if ($orderableKey === '') {
                throw ValidationException::withMessages([
                    'quotable_type' => 'This quote customer type cannot be used on an order.',
                ]);
            }

            $before = $this->auditLogService->snapshot($locked);

            $order = $this->orderCreator->create([
                'orderable_type' => $orderableKey,
                'orderable_id' => $locked->quotable_id,
                'title' => $locked->title,
                'description' => $locked->description,
                'notes' => trim("Converted from quote {$locked->quote_number}.\n".($locked->notes ?? '')),
                'subtotal' => $locked->subtotal,
                'discount_amount' => $locked->discount_amount,
                'tax_amount' => $locked->tax_amount,
                'total_amount' => $locked->total_amount,
                'currency' => $locked->currency,
                'ordered_at' => now(),
                'status_id' => OrderStatus::where('title', 'Pending')->value('id'),
                'meta' => ['quote_id' => $locked->id, 'quote_number' => $locked->quote_number],
            ], $actor->id);

            $locked->forceFill([
                'status_id' => QuoteStatus::idForKey(QuoteStatus::KEY_CONVERTED),
                'converted_order_id' => $order->id,
                'converted_at' => now(),
                'updated_by' => $actor->id,
            ])->save();
            $locked->unsetRelation('status');

            $this->auditLogService->record(
                Log::ACTION_CONVERT_QUOTE,
                $actor,
                $locked,
                [
                    'before' => $before,
                    'after' => $this->auditLogService->snapshot($locked),
                ],
            );

            return $order;
        });
    }
}
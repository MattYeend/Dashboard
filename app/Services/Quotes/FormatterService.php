<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Models\Quote;
use App\Services\CurrencyFormatterService;

class FormatterService
{
    public function __construct(
        private readonly QuotableTypeRegistryService $registry,
        private readonly CurrencyFormatterService $currencyFormatter
    ) {}

    /**
     * Format a single quote with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Quote $quote): array
    {
        return [
            'id' => $quote->id,
            'quotable_type' => $quote->quotable_type,
            'quotable_id' => $quote->quotable_id,
            'quote_number' => $quote->quote_number,
            'title' => $quote->title,
            'description' => $quote->description,
            'notes' => $quote->notes,
            'terms' => $quote->terms,
            'subtotal' => $quote->subtotal,
            'discount_amount' => $quote->discount_amount,
            'tax_amount' => $quote->tax_amount,
            'total_amount' => $quote->total_amount,
            'currency' => $quote->currency,
            'formatted_total' => $this->currencyFormatter->format(
                (float) $quote->total_amount,
                $quote->currency,
            ),
            'issued_at' => $quote->issued_at?->format('Y-m-d'),
            'valid_until' => $quote->valid_until?->format('Y-m-d'),
            'sent_at' => $quote->sent_at?->format('Y-m-d\TH:i'),
            'accepted_at' => $quote->accepted_at?->format('Y-m-d\TH:i'),
            'declined_at' => $quote->declined_at?->format('Y-m-d\TH:i'),
            'expired_at' => $quote->expired_at?->format('Y-m-d\TH:i'),
            'converted_at' => $quote->converted_at?->format('Y-m-d\TH:i'),
            'status_id' => $quote->status_id,
            'converted_order_id' => $quote->converted_order_id,
            'meta' => $quote->meta,
            'created_at' => $quote->created_at,
            'updated_at' => $quote->updated_at,
            'deleted_at' => $quote->deleted_at,
            'restored_at' => $quote->restored_at,
            'status' => $quote->status ? [
                'id' => $quote->status->id,
                'title' => $quote->status->title,
                'system_key' => $quote->status->system_key,
                'background_colour' => $quote->status->background_colour,
                'text_colour' => $quote->status->text_colour,
            ] : null,
            'converted_order' => $quote->convertedOrder ? [
                'id' => $quote->convertedOrder->id,
                'order_number' => $quote->convertedOrder->order_number,
            ] : null,
            'creator' => $quote->creator ? ['id' => $quote->creator->id, 'name' => $quote->creator->name] : null,
            'updater' => $quote->updater ? ['id' => $quote->updater->id, 'name' => $quote->updater->name] : null,
            'deleter' => $quote->deleter ? ['id' => $quote->deleter->id, 'name' => $quote->deleter->name] : null,
            'restorer' => $quote->restorer ? ['id' => $quote->restorer->id, 'name' => $quote->restorer->name] : null,
            'quotable_type_label' => $quote->quotable_type
                ? ($this->registry->labelForModel($quote->quotable_type) ?? class_basename($quote->quotable_type))
                : null,
            'quotable_name' => $quote->quotable
                ? ($quote->quotable->name ?? $quote->quotable->title ?? '#'.$quote->quotable->id)
                : null,
            'quotable_type_key' => $this->registry->keyForModel($quote->quotable_type),
        ];
    }
}

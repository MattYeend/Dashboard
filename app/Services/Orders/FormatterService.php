<?php

namespace App\Services\Orders;

use App\Models\Order;

class FormatterService
{
    public function __construct(
        private readonly OrderableTypeRegistryService $registry,
    ) {}

    /**
     * Format a single order with all data.
     *
     * @return array<string, mixed>
     */
    public function format(
        Order $order
    ): array {
        return [
            'id' => $order->id,
            'orderable_type' => $order->orderable_type,
            'orderable_id' => $order->orderable_id,
            'order_number' => $order->order_number,
            'title' => $order->title,
            'description' => $order->description,
            'notes' => $order->notes,
            'subtotal' => $order->subtotal,
            'discount_amount' => $order->discount_amount,
            'tax_amount' => $order->tax_amount,
            'total_amount' => $order->total_amount,
            'ordered_at' => $order->ordered_at?->format('Y-m-d\TH:i'),
            'due_at' => $order->due_at?->format('Y-m-d\TH:i'),
            'completed_at' => $order->completed_at?->format('Y-m-d\TH:i'),
            'status_id' => $order->status_id,
            'meta' => $order->meta,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'deleted_at' => $order->deleted_at,
            'restored_at' => $order->restored_at,
            'status' => $order->status ? ['id' => $order->status->id, 'title' => $order->status->title] : null,
            'creator' => $order->creator ? ['id' => $order->creator->id, 'name' => $order->creator->name] : null,
            'updater' => $order->updater ? ['id' => $order->updater->id, 'name' => $order->updater->name] : null,
            'deleter' => $order->deleter ? ['id' => $order->deleter->id, 'name' => $order->deleter->name] : null,
            'restorer' => $order->restorer ? ['id' => $order->restorer->id, 'name' => $order->restorer->name] : null,
            'orderable_type_label' => $order->orderable_type
                ? ($this->registry->labelForModel($order->orderable_type) ?? class_basename($order->orderable_type))
                : null,
            'orderable_name' => $order->orderable
                ? ($order->orderable->name
                    ?? $order->orderable->title
                    ?? '#'.$order->orderable->id)
                : null,
            'orderable_type_key' => $this->registry->keyForModel($order->orderable_type),
        ];
    }
}

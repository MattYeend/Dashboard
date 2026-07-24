<?php

namespace App\Services\OrderStatuses;

use App\Models\OrderStatus;

class FormatterService
{
    /**
     * Format a single order status with all data.
     *
     * @return array<string, mixed>
     */
    public function format(
        OrderStatus $orderStatus
    ): array {
        return [
            'id' => $orderStatus->id,
            'title' => $orderStatus->title,
            'description' => $orderStatus->description,
            'background_colour' => $orderStatus->background_colour,
            'text_colour' => $orderStatus->text_colour,
            'meta' => $orderStatus->meta,
            'created_at' => $orderStatus->created_at,
            'updated_at' => $orderStatus->updated_at,
            'deleted_at' => $orderStatus->deleted_at,
            'restored_at' => $orderStatus->restored_at,
            'created_by' => $orderStatus->created_by,
            'updated_by' => $orderStatus->updated_by,
            'deleted_by' => $orderStatus->deleted_by,
            'restored_by' => $orderStatus->restored_by,
            'creator' => $orderStatus->creator ? ['id' => $orderStatus->creator->id, 'name' => $orderStatus->creator->name] : null,
            'updater' => $orderStatus->updater ? ['id' => $orderStatus->updater->id, 'name' => $orderStatus->updater->name] : null,
            'deleter' => $orderStatus->deleter ? ['id' => $orderStatus->deleter->id, 'name' => $orderStatus->deleter->name] : null,
            'restorer' => $orderStatus->restorer ? ['id' => $orderStatus->restorer->id, 'name' => $orderStatus->restorer->name] : null,
        ];
    }
}

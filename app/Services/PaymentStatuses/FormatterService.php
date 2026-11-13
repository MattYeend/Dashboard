<?php

declare(strict_types=1);

namespace App\Services\PaymentStatuses;

use App\Models\PaymentStatus;

class FormatterService
{
    /**
     * Format a single payment status with all data.
     *
     * @return array<string, mixed>
     */
    public function format(PaymentStatus $paymentStatus): array
    {
        return [
            'id' => $paymentStatus->id,
            'title' => $paymentStatus->title,
            'description' => $paymentStatus->description,
            'background_colour' => $paymentStatus->background_colour,
            'text_colour' => $paymentStatus->text_colour,
            'system_key' => $paymentStatus->system_key,
            'is_system' => $paymentStatus->isSystem(),
            'meta' => $paymentStatus->meta,
            'created_at' => $paymentStatus->created_at,
            'updated_at' => $paymentStatus->updated_at,
            'deleted_at' => $paymentStatus->deleted_at,
            'restored_at' => $paymentStatus->restored_at,
            'creator' => $paymentStatus->creator ? ['id' => $paymentStatus->creator->id, 'name' => $paymentStatus->creator->name] : null,
            'updater' => $paymentStatus->updater ? ['id' => $paymentStatus->updater->id, 'name' => $paymentStatus->updater->name] : null,
            'deleter' => $paymentStatus->deleter ? ['id' => $paymentStatus->deleter->id, 'name' => $paymentStatus->deleter->name] : null,
            'restorer' => $paymentStatus->restorer ? ['id' => $paymentStatus->restorer->id, 'name' => $paymentStatus->restorer->name] : null,
        ];
    }
}

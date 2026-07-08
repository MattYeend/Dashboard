<?php

namespace App\Services\Plans;

use App\Models\Plan;

class FormatterService
{
    /**
     * Format a single plan with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Plan $plan): array
    {
        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'description' => $plan->description,
            'price_per_user_per_month' => $plan->price_per_user_per_month,
            'is_active' => $plan->is_active,
            'created_at' => $plan->created_at,
            'updated_at' => $plan->updated_at,
            'deleted_at' => $plan->deleted_at,
            'restored_at' => $plan->restored_at,
            'creator' => $plan->creator ? ['id' => $plan->creator->id, 'name' => $plan->creator->name] : null,
            'updater' => $plan->updater ? ['id' => $plan->updater->id, 'name' => $plan->updater->name] : null,
            'deleter' => $plan->deleter ? ['id' => $plan->deleter->id, 'name' => $plan->deleter->name] : null,
            'restorer' => $plan->restorer ? ['id' => $plan->restorer->id, 'name' => $plan->restorer->name] : null,
        ];
    }
}

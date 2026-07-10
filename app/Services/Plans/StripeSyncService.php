<?php

namespace App\Services\Plans;

use App\Models\Plan;

class StripeSyncService
{
    /**
     * Sync a Plan's stored price from a Stripe 'price.updated' payload.
     */
    public function syncPrice(array $price): void
    {
        $plan = Plan::where('stripe_price_id', $price['id'])->first();

        if (! $plan) {
            return;
        }

        $plan->update([
            'price_per_user_per_month' => $price['unit_amount'],
            'updated_by' => null,
        ]);
    }

    /**
     * Sync a Plan's active status from a Stripe 'product.updated' payload.
     */
    public function syncActiveStatus(array $product): void
    {
        $plan = Plan::where('stripe_product_id', $product['id'])->first();

        if (! $plan) {
            return;
        }

        $plan->update([
            'is_active' => $product['active'],
            'updated_by' => null,
        ]);
    }
}

<?php

namespace App\Services\Plans;

use App\Models\Log;
use App\Models\Plan;
use App\Services\AuditLogService;

class StripeSyncService
{
    /**
     * Inject the required services into the Stripe sync service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Sync a Plan's stored price from a Stripe 'price.updated' payload.
     */
    public function syncPrice(array $price): void
    {
        $plan = Plan::where('stripe_price_id', $price['id'])->first();

        if (! $plan) {
            return;
        }

        $before = $this->auditLogService->snapshot($plan);

        $plan->update([
            'price_per_user_per_month' => $price['unit_amount'],
        ]);

        $this->auditLogService->record(
            Log::ACTION_UPDATE_PLAN,
            null,
            $plan,
            [
                'before' => $before,
                'after' => $this->auditLogService->snapshot($plan->fresh()),
                'source' => 'stripe_webhook',
            ],
        );
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

        $before = $this->auditLogService->snapshot($plan);

        $plan->update([
            'is_active' => $product['active'],
        ]);

        $this->auditLogService->record(
            Log::ACTION_UPDATE_PLAN,
            null,
            $plan,
            [
                'before' => $before,
                'after' => $this->auditLogService->snapshot($plan->fresh()),
                'source' => 'stripe_webhook',
            ],
        );
    }
}

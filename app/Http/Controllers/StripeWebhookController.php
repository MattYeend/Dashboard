<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Plans\StripeSyncService;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierWebhookController
{
    public function __construct(
        protected StripeSyncService $stripeSyncService
    ) {
        parent::__construct();
    }

    /**
     * Handle a Stripe 'customer.subscription.created' webhook event.
     *
     * Backfills the local plan_id on the newly created subscription row,
     * since Cashier's own handler has no knowledge of the local Plan model.
     */
    protected function handleCustomerSubscriptionCreated(
        array $payload
    ): Response {
        $response = parent::handleCustomerSubscriptionCreated(
            $payload
        );

        $this->syncSubscriptionPlan(
            $payload
        );

        return $response;
    }

    /**
     * Handle a Stripe 'customer.subscription.updated' webhook event.
     *
     * Keeps plan_id in sync when a subscription is swapped to a
     * different price (e.g. an upgrade or downgrade).
     */
    protected function handleCustomerSubscriptionUpdated(
        array $payload
    ): Response {
        $response = parent::handleCustomerSubscriptionUpdated(
            $payload
        );

        $this->syncSubscriptionPlan(
            $payload
        );

        return $response;
    }

    /**
     * Handle a Stripe 'price.updated' webhook event.
     *
     * Keeps the local Plan's price in sync whenever the price is
     * changed on the Stripe dashboard, rather than only locally.
     */
    protected function handlePriceUpdated(
        array $payload
    ): Response {
        $this->stripeSyncService->syncPrice(
            $payload['data']['object']
        );

        return $this->successMethod();
    }

    /**
     * Handle a Stripe 'product.updated' webhook event.
     *
     * Keeps the local Plan's active status in sync whenever the product is
     * changed on the Stripe dashboard, rather than only locally.
     */
    protected function handleProductUpdated(
        array $payload
    ): Response {
        $this->stripeSyncService->syncActiveStatus(
            $payload['data']['object']
        );

        return $this->successMethod();
    }

    /**
     * Sync the local plan_id on a subscription row from a Stripe
     * subscription payload, matching on the subscription's current price.
     */
    private function syncSubscriptionPlan(
        array $payload
    ): void {
        $priceId = $payload['data']['object']['items']['data'][0]['price']['id'] ?? null;
        $stripeSubscriptionId = $payload['data']['object']['id'] ?? null;

        if (! $priceId || ! $stripeSubscriptionId) {
            return;
        }

        $plan = Plan::where(
            'stripe_price_id',
            $priceId
        )->first();

        if (! $plan) {
            return;
        }

        Subscription::where(
            'stripe_id',
            $stripeSubscriptionId
        )
            ->update(['plan_id' => $plan->id]);
    }
}

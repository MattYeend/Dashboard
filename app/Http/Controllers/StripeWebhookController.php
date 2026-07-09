<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierWebhookController
{
    /**
     * Handle a Stripe 'price.updated' webhook event.
     *
     * Keeps the local Plan's price in sync whenever the price is
     * changed on the Stripe dashboard, rather than only locally.
     */
    public function handlePriceUpdated(array $payload): Response
    {
        $price = $payload['data']['object'];

        $plan = Plan::where('stripe_price_id', $price['id'])->first();

        if ($plan) {
            $plan->update([
                'price_per_month' => $price['unit_amount'],
            ]);
        }

        return $this->successMethod();
    }

    /**
     * Handle a Stripe 'product.updated' webhook event.
     *
     * Keeps the local Plan's active status in sync whenever the product is
     * changed on the Stripe dashboard, rather than only locally.
     */
    public function handleProductUpdated(array $payload): Response
    {
        $stripeProduct = $payload['data']['object'];

        $plan = Plan::where('stripe_product_id', $stripeProduct['id'])->first();

        if ($plan) {
            $plan->update([
                'is_active' => $stripeProduct['active'],
            ]);
        }

        return $this->successMethod();
    }
}

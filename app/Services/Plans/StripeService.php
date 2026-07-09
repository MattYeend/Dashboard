<?php

namespace App\Services\Plans;

use App\Models\Plan;
use Laravel\Cashier\Cashier;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    /**
     * Create a Stripe Product and its associated Price for the given Plan,
     * then store the returned Stripe IDs back onto the Plan.
     *
     * @throws ApiErrorException
     */
    public function createProductWithPrice(Plan $plan): Plan
    {
        $stripe = Cashier::stripe();

        // Stripe products represent the sellable thing, prices are versioned
        // amounts attached to a product. We store both IDs locally so future
        // price changes create a new Stripe Price rather than mutating this one.
        $product = $stripe->products->create([
            'name' => $plan->name,
            'description' => $plan->description,
            'active' => (bool) $plan->is_active,
        ]);

        $price = $stripe->prices->create([
            'product' => $product->id,
            'unit_amount' => $plan->price_per_user_per_month,
            'currency' => config('cashier.currency', 'gbp'),
            'recurring' => [
                'interval' => 'month',
            ],
        ]);

        $plan->update([
            'stripe_product_id' => $product->id,
            'stripe_price_id' => $price->id,
        ]);

        return $plan;
    }
}

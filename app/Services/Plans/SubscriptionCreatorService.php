<?php

namespace App\Services\Plans;

use App\Models\Organisation;
use App\Models\Plan;
use App\Models\Subscription;

class SubscriptionCreatorService
{
    /**
     * Inject the required services into the subscription creator service.
     */
    public function __construct(
        protected readonly SeatCalculatorService $seatCalculatorService,
    ) {}

    /**
     * Create a new Cashier subscription for the organisation, quantity set
     * from the current seat count.
     *
     * plan_id is set explicitly after creation - nothing currently observes
     * Stripe webhooks or the subscription's stripe_price to populate it,
     * so without this line Subscription::plan() would return null.
     */
    public function create(
        Organisation $organisation,
        Plan $plan,
        string $paymentMethod
    ): Subscription {
        $seats = max($this->seatCalculatorService->currentSeatCount($organisation), 1);

        /** @var Subscription $subscription */
        $subscription = $organisation
            ->newSubscription($plan->slug, $plan->stripe_price_id)
            ->quantity($seats)
            ->create($paymentMethod);

        $subscription->update(['plan_id' => $plan->id]);

        return $subscription;
    }
}

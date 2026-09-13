<?php

namespace App\Services\Plans;

use App\Models\Organisation;
use App\Models\Plan;
use Laravel\Cashier\Subscription;

class SubscriptionCreatorService
{
    /** 
     * Inject the required services into the subscription creator service. 
     */
    public function __construct(
        protected readonly SeatCalculatorService $seatCalculatorService,
    ) {}

    /** 
     * Create a new Cashier subscription for the organisation, quantity set from the current seat count.
     */
    public function create(
        Organisation $organisation,
        Plan $plan,
        string $paymentMethod
    ): Subscription {
        $seats = max($this->seatCalculatorService->currentSeatCount($organisation), 1);

        return $organisation
            ->newSubscription($plan->slug, $plan->stripe_price_id)
            ->quantity($seats)
            ->create($paymentMethod);
    }
}

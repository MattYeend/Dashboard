<?php

namespace App\Services\Plans;

use App\Models\Organisation;
use App\Models\Plan;

class SeatCalculatorService
{
    /** 
     * Count the active members billed as seats for the given organisation. 
     */
    public function currentSeatCount(Organisation $organisation): int
    {
        return $organisation->activeUsers()->count();
    }

    /** 
     * Calculate the total monthly price for a plan given a seat count.
     */
    public function calculateTotal(Plan $plan, int $seats): int
    {
        return $plan->price_per_user_per_month * $seats;
    }
}
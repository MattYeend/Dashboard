<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    /**
     * Payment is up to date.
     */
    public const PAYMENT_STATUS_CURRENT = 'current';

    /**
     * The most recent invoice failed to collect payment.
     */
    public const PAYMENT_STATUS_PAST_DUE = 'past_due';

    /**
     * Stripe has marked the subscription unpaid after repeated failures.
     */
    public const PAYMENT_STATUS_UNPAID = 'unpaid';

    /**
     * Get the local Plan this subscription corresponds to.
     *
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Determine whether the subscription's last invoice failed to collect payment.
     */
    public function isPastDue(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAST_DUE;
    }

    /**
     * Determine whether Stripe has marked the subscription as unpaid.
     */
    public function isUnpaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_UNPAID;
    }

    /**
     * Get the Organisation this subscription belongs to.
     *
     * @return BelongsTo<Organisation, $this>
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}

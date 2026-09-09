<?php

namespace App\Services\Plans;

use App\Models\Log;
use App\Models\Subscription;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentFailedReminderNotification;
use App\Notifications\PaymentRecoveredNotification;
use Illuminate\Support\Facades\Notification;

class DunningService
{
    /**
     * How long after a failed payment the follow-up reminder is sent.
     */
    private const REMINDER_DELAY_DAYS = 3;

    /**
     * Flag the subscription as past due, notify the user, and schedule a
     * follow-up reminder. Repeat webhook deliveries for the same ongoing
     * failure are still audit-logged but don't re-notify the user.
     */
    public function handlePaymentFailed(Subscription $subscription): void
    {
        $before = $subscription->payment_status;
        $wasAlreadyFailing = $subscription->isPastDue() || $subscription->isUnpaid();

        $subscription->payment_status = Subscription::PAYMENT_STATUS_PAST_DUE;
        $subscription->save();

        $organisation = $subscription->organisation;

        if ($organisation !== null && ! $wasAlreadyFailing) {
            $recipients = $organisation->activeUsers()->wherePivotIn('role', ['owner', 'admin'])->get();

            Notification::send($recipients, new PaymentFailedNotification($subscription));

            $reminder = new PaymentFailedReminderNotification($subscription);
            $reminder->delay(now()->addDays(self::REMINDER_DELAY_DAYS));
            Notification::send($recipients, $reminder);
        }

        Log::log(
            Log::ACTION_SUBSCRIPTION_PAYMENT_FAILED,
            [
                'subscription_id' => $subscription->id,
                'organisation_id' => $subscription->organisation_id,
                'before' => $before,
                'after' => $subscription->payment_status,
            ],
            null,
            null,
        );
    }

    /**
     * Clear a past-due flag and notify the user their payment recovered.
     * Ordinary renewal payments (where the subscription was already
     * `current`) are ignored so recovery notifications aren't sent on
     * every normal billing cycle.
     */
    public function handlePaymentSucceeded(Subscription $subscription): void
    {
        if ($subscription->payment_status === Subscription::PAYMENT_STATUS_CURRENT) {
            return;
        }

        $before = $subscription->payment_status;

        $subscription->payment_status = Subscription::PAYMENT_STATUS_CURRENT;
        $subscription->save();

        $organisation = $subscription->organisation;

        if ($organisation !== null) {
            $recipients = $organisation->activeUsers()->wherePivotIn('role', ['owner', 'admin'])->get();

            Notification::send($recipients, new PaymentRecoveredNotification($subscription));
        }

        Log::log(
            Log::ACTION_SUBSCRIPTION_PAYMENT_RECOVERED,
            [
                'subscription_id' => $subscription->id,
                'organisation_id' => $subscription->organisation_id,
                'before' => $before,
                'after' => $subscription->payment_status,
            ],
            null,
            null,
        );
    }
}

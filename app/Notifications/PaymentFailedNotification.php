<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Throwable;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance for the given subscription.
     */
    public function __construct(protected readonly Subscription $subscription) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('We were unable to process your payment')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('We tried to collect payment for your subscription, but the charge did not go through.')
            ->line('Please update your payment details to avoid any interruption to your account.')
            ->action('Update payment details', $this->billingPortalUrl($notifiable))
            ->line('If you have already updated your details, no further action is needed.');
    }

    /**
     * Get the array representation of the notification for the database channel.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment failed',
            'body' => 'We were unable to collect payment for your subscription. Please update your payment details.',
            'action_url' => $this->billingPortalUrl($notifiable),
            'subject_type' => Subscription::class,
            'subject_id' => $this->subscription->id,
        ];
    }

    /**
     * Resolve a Stripe billing portal URL for the notifiable, falling back
     * to the plans page if the portal session cannot be created.
     */
    private function billingPortalUrl(object $notifiable): string
    {
        try {
            return $notifiable->billingPortalUrl(route('plans.index'));
        } catch (Throwable) {
            return route('plans.index');
        }
    }
}

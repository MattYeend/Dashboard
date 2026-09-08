<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Throwable;

class PaymentFailedReminderNotification extends Notification implements ShouldQueue
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
            ->subject('Reminder: your payment still needs attention')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('We still haven\'t been able to collect payment for your subscription.')
            ->line('Please update your payment details as soon as possible to keep your account active.')
            ->action('Update payment details', $this->billingPortalUrl($notifiable));
    }

    /**
     * Get the array representation of the notification for the database channel.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment still needs attention',
            'body' => 'We still haven\'t been able to collect payment for your subscription.',
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

<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentRecoveredNotification extends Notification implements ShouldQueue
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
            ->subject('Your payment has been received')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Thanks, we\'ve successfully collected payment and your subscription is back up to date.')
            ->line('No further action is needed.');
    }

    /**
     * Get the array representation of the notification for the database channel.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment received',
            'body' => 'Your payment has been received and your subscription is up to date.',
            'action_url' => route('plans.index'),
            'subject_type' => Subscription::class,
            'subject_id' => $this->subscription->id,
        ];
    }
}

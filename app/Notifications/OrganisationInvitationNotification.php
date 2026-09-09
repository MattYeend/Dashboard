<?php

namespace App\Notifications;

use App\Models\Organisation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class OrganisationInvitationNotification extends Notification
{
    use Queueable;

    /**
     * Inject the invitation context into the notification.
     */
    public function __construct(
        protected readonly Organisation $organisation,
        protected readonly string $invitationToken,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation, using a signed URL so the link
     * can't be tampered with or replayed after expiry.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'organisations.invitations.accept',
            now()->addDays(7),
            ['token' => $this->invitationToken],
        );

        return (new MailMessage)
            ->subject("You've been invited to join {$this->organisation->name}")
            ->line("You've been invited to join {$this->organisation->name}.")
            ->action('Accept invitation', $url)
            ->line('This invitation link expires in 7 days.');
    }
}

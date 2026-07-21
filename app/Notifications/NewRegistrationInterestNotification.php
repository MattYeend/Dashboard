<?php

namespace App\Notifications;

use App\Models\RegistrationInterest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRegistrationInterestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected RegistrationInterest $interest) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New registration interest')
            ->line("{$this->interest->name} ({$this->interest->email}) has registered interest.")
            ->when($this->interest->company, fn (MailMessage $mail) => $mail->line("Company: {$this->interest->company}"))
            ->when($this->interest->message, fn (MailMessage $mail) => $mail->line("Message: {$this->interest->message}"))
            ->action('View interest', route('registration-interests.show', $this->interest));
    }
}

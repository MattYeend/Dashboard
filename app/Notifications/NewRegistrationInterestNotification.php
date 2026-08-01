<?php

namespace App\Notifications;

use App\Models\RegistrationInterest;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notifies admins of a new registration interest submission.
 *
 * Delivers via mail (to notify admins immediately) and database
 * (so it appears in the in-app notifications list).
 */
class NewRegistrationInterestNotification extends BaseNotification
{
    /**
     * @param  RegistrationInterest  $interest  The submitted interest record.
     */
    public function __construct(protected RegistrationInterest $interest) {}

    /**
     * Delivery channels for this notification.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Build the e-mail representation of this notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New registration interest')
            ->line("{$this->interest->name} ({$this->interest->email}) has registered interest.")
            ->when($this->interest->company, fn (MailMessage $mail) => $mail->line("Company: {$this->interest->company}"))
            ->when($this->interest->message, fn (MailMessage $mail) => $mail->line("Message: {$this->interest->message}"))
            ->action('View interest', route('registration-interests.show', $this->interest));
    }

    /**
     * Machine-readable category used for filtering/icons in the UI.
     */
    protected function type(): string
    {
        return 'registration_interest';
    }

    /**
     * Heading shown in the notifications list.
     */
    protected function title(): string
    {
        return 'New registration interest';
    }

    /**
     * Body text shown in the notifications list.
     */
    protected function body(): string
    {
        return "{$this->interest->name} ({$this->interest->email}) has registered interest.";
    }

    /**
     * URL the user is taken to when they click the notification.
     */
    protected function actionUrl(): ?string
    {
        return route('registration-interests.show', $this->interest);
    }

    /**
     * Related model class, used to link the notification back to its subject.
     */
    protected function subjectType(): ?string
    {
        return RegistrationInterest::class;
    }

    /**
     * Primary key of the related model.
     */
    protected function subjectId(): ?int
    {
        return $this->interest->id;
    }
}

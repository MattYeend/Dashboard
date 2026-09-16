<?php

namespace App\Notifications;

use App\Models\OrganisationDataExport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrganisationDataExportReady extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly OrganisationDataExport $export,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $organisationName = $this->export->organisation->name;

        return [
            'organisation_id' => $this->export->organisation_id,
            'organisation_name' => $organisationName,
            'message' => "Your data export for {$organisationName} is ready to download.",
            'download_url' => route('organisations.data-privacy.download', [
                'organisation' => $this->export->organisation_id,
                'export' => $this->export->id,
            ]),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $organisationName = $this->export->organisation->name;

        return (new MailMessage())
            ->subject('Your organisation data export is ready')
            ->line("Your data export for {$organisationName} is ready to download.")
            ->action('Download export', route('organisations.data-privacy.download', [
                'organisation' => $this->export->organisation_id,
                'export' => $this->export->id,
            ]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

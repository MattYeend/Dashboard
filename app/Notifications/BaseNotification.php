<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Shared base for all database-backed notifications.
 *
 * Concrete notifications extend this class and implement the abstract
 * methods below to describe how they should appear in the in-app
 * notifications table. Channels other than 'database' (e.g. 'mail')
 * should be added by overriding via() in the child class.
 */
abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Default delivery channel(s) for this notification.
     *
     * Child classes that need additional channels (e.g. mail) should
     * override this method and include 'database' explicitly if they
     * still want the in-app record.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Shape of the record stored in the notifications table.
     *
     * Pulls its values from the abstract methods below, so each
     * concrete notification only needs to supply the content, not
     * the storage structure.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->type(),
            'title' => $this->title(),
            'body' => $this->body(),
            'action_url' => $this->actionUrl(),
            'subject_type' => $this->subjectType(),
            'subject_id' => $this->subjectId(),
        ];
    }

    /**
     * Short machine-readable category for this notification
     * (e.g. 'registration_interest'), used for filtering/icons in the UI.
     */
    abstract protected function type(): string;

    /**
     * Human-readable heading shown in the notifications list.
     */
    abstract protected function title(): string;

    /**
     * Human-readable body text shown in the notifications list.
     */
    abstract protected function body(): string;

    /**
     * URL the user is taken to when they click the notification, if any.
     */
    abstract protected function actionUrl(): ?string;

    /**
     * Fully qualified class name of the model this notification relates to,
     * if any (e.g. RegistrationInterest::class).
     */
    abstract protected function subjectType(): ?string;

    /**
     * Primary key of the related model, if any.
     */
    abstract protected function subjectId(): ?int;
}

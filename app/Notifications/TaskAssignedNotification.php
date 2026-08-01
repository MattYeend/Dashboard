<?php

namespace App\Notifications;

use App\Models\Task;

class TaskAssignedNotification extends BaseNotification
{
    /**
     * @param Task $task The task that has been assigned.
     */
    public function __construct(protected Task $task) {}

    /**
     * Machine-readable category used for filtering/icons in the UI.
     */
    protected function type(): string
    {
        return 'task_assigned';
    }

    /**
     * Heading shown in the notifications list.
     */
    protected function title(): string
    {
        return 'New task assigned';
    }

    /**
     * Body text shown in the notifications list.
     */
    protected function body(): string
    {
        return "You have been assigned: {$this->task->title}";
    }

    /**
     * URL the user is taken to when they click the notification.
     */
    protected function actionUrl(): ?string
    {
        return route('tasks.show', $this->task->id);
    }

    /**
     * Related model class, used to link the notification back to its subject.
     */
    protected function subjectType(): ?string
    {
        return Task::class;
    }

    /**
     * Primary key of the related model.
     */
    protected function subjectId(): ?int
    {
        return $this->task->id;
    }
}
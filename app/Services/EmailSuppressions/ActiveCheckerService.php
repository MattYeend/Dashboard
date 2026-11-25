<?php

declare(strict_types=1);

namespace App\Services\EmailSuppressions;

use App\Models\EmailSuppression;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class ActiveCheckerService
{
    /**
     * Inject the required services into the active checker service.
     */
    public function __construct(
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if email suppression is active (not soft-deleted).
     */
    public function isActive(EmailSuppression $emailSuppression): bool
    {
        return ! $emailSuppression->trashed();
    }

    /**
     * Check if email suppression is soft-deleted.
     */
    public function isTrashed(EmailSuppression $emailSuppression): bool
    {
        return $emailSuppression->trashed();
    }

    /**
     * Check if email suppression can be edited.
     */
    public function isEditable(EmailSuppression $emailSuppression): bool
    {
        return $this->isActive($emailSuppression);
    }

    /**
     * Check if email suppression can be soft-deleted.
     */
    public function canBeDeleted(EmailSuppression $emailSuppression): bool
    {
        return $this->isActive($emailSuppression);
    }

    /**
     * Check if email suppression is active (not soft-deleted) and can be modified.
     */
    public function canBeModified(EmailSuppression $emailSuppression): bool
    {
        return $this->isActive($emailSuppression);
    }

    /**
     * Check if email suppression is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        EmailSuppression $emailSuppression
    ): bool {
        return $this->isTrashed($emailSuppression);
    }

    /**
     * Check if the user can modify, restore or force-delete the email
     * suppression based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        EmailSuppression $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

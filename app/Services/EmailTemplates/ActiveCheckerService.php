<?php

declare(strict_types=1);

namespace App\Services\EmailTemplates;

use App\Models\EmailTemplate;
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
     * Check if email template is active (not soft-deleted).
     */
    public function isActive(EmailTemplate $emailTemplate): bool
    {
        return ! $emailTemplate->trashed();
    }

    /**
     * Check if email template is soft-deleted.
     */
    public function isTrashed(EmailTemplate $emailTemplate): bool
    {
        return $emailTemplate->trashed();
    }

    /**
     * Check if email template can be edited.
     */
    public function isEditable(EmailTemplate $emailTemplate): bool
    {
        return $this->isActive($emailTemplate);
    }

    /**
     * Check if email template is active and can be updated/deleted.
     */
    public function canBeModified(EmailTemplate $emailTemplate): bool
    {
        return $this->isActive($emailTemplate);
    }

    /**
     * Check if email template is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        EmailTemplate $emailTemplate
    ): bool {
        return $this->isTrashed($emailTemplate);
    }

    /**
     * Check if the user can modify, restore or force-delete the email
     * template based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        EmailTemplate $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

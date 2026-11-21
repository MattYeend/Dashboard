<?php

declare(strict_types=1);

namespace App\Services\EmailTemplates;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\Concerns\ChecksOrganisationBoundary;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    use ChecksOrganisationBoundary;

    /**
     * Inject the required services into the policy authorisation service.
     */
    public function __construct(
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if user is a regular user, admin, or super admin.
     */
    public function isUser(User $user): bool
    {
        return $this->roleChecker->isUser($user);
    }

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    /**
     * Check if email template is active (not soft-deleted).
     */
    public function isActive(EmailTemplate $emailTemplate): bool
    {
        return $this->activeChecker->isActive($emailTemplate);
    }

    /**
     * Check if email template is soft-deleted.
     */
    public function isTrashed(EmailTemplate $emailTemplate): bool
    {
        return $this->activeChecker->isTrashed($emailTemplate);
    }

    /**
     * Determine whether the user can view any email templates.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view email templates');
    }

    /**
     * Determine whether the user can create email templates.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create email templates');
    }

    /**
     * Determine whether the user can view the email template.
     */
    public function canView(User $actor, EmailTemplate $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view email templates')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the email template.
     */
    public function canUpdate(User $actor, EmailTemplate $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit email templates')
            && $this->activeChecker->isEditable($target);
    }

    /**
     * Determine whether the user can delete the email template.
     */
    public function canDelete(User $actor, EmailTemplate $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete email templates')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the email template.
     */
    public function canRestore(User $actor, EmailTemplate $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore email templates')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the email template.
     */
    public function canForceDelete(User $actor, EmailTemplate $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target) || $this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }

    /**
     * Determine whether the user can import email templates.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import email templates');
    }

    /**
     * Determine whether the user can export email templates.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export email templates');
    }

    /**
     * Determine whether the email template was created by a user who outranks the actor.
     *
     * Prevents admins from managing email templates created by super admins.
     */
    private function targetOutranksActor(
        User $actor,
        EmailTemplate $target
    ): bool {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $creator = $target->loadMissing('creator')->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($creator);
    }
}
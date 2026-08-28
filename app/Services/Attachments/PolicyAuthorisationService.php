<?php

namespace App\Services\Attachments;

use App\Models\Attachment;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    /**
     * Inject the required services into the policy authorisation service.
     */
    public function __construct(
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Determine whether the user can create attachments.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create attachments');
    }

    /**
     * Determine whether the user can download the given attachment.
     *
     * Checked on every download request (not just once at upload
     * time), so a permission revoked after the file was uploaded
     * takes effect immediately.
     */
    public function canDownload(User $actor, Attachment $target): bool
    {
        return $actor->can('download attachments') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the given attachment.
     */
    public function canDelete(User $actor, Attachment $target): bool
    {
        return $actor->can('delete attachments') && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the given attachment.
     */
    public function canRestore(User $actor, Attachment $target): bool
    {
        return $actor->can('restore attachments') && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the given
     * attachment (and its underlying file).
     */
    public function canForceDelete(User $actor, Attachment $target): bool
    {
        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }
}

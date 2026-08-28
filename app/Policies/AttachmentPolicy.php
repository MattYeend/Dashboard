<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;
use App\Services\Attachments\PolicyAuthorisationService;

class AttachmentPolicy
{
    /**
     * The authorisation service handling permission checks.
     */
    protected PolicyAuthorisationService $authorisationService;

    /**
     * Inject the required service into the policy.
     */
    public function __construct(
        PolicyAuthorisationService $authorisationService
    ) {
        $this->authorisationService = $authorisationService;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->canCreate($user);
    }

    /**
     * Determine whether the user can download the model's file.
     *
     * This is the sole gate standing between a request and the
     * physical file on disk - the download route has no other
     * authorisation check.
     */
    public function download(User $user, Attachment $attachment): bool
    {
        return $this->authorisationService->canDownload($user, $attachment);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Attachment $attachment): bool
    {
        return $this->authorisationService->canDelete($user, $attachment);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Attachment $attachment): bool
    {
        return $this->authorisationService->canRestore($user, $attachment);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Attachment $attachment): bool
    {
        return $this->authorisationService->canForceDelete($user, $attachment);
    }
}

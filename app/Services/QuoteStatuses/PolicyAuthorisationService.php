<?php

declare(strict_types=1);

namespace App\Services\QuoteStatuses;

use App\Models\QuoteStatus;
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
     * Check if user is admin or super admin.
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    /**
     * Determine whether the user can view any quote statuses.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any quote status');
    }

    /**
     * Determine whether the user can create quote statuses.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create quote status');
    }

    /**
     * Determine whether the user can view the quote status.
     */
    public function canView(User $actor, QuoteStatus $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('view quote status')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the quote status.
     */
    public function canUpdate(User $actor, QuoteStatus $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('edit quote status')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the quote status.
     */
    public function canDelete(User $actor, QuoteStatus $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('delete quote status')
            && $this->activeChecker->canBeDeleted($target);
    }

    /**
     * Determine whether the user can restore the quote status.
     */
    public function canRestore(User $actor, QuoteStatus $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('restore quote status')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the quote status.
     */
    public function canForceDelete(User $actor, QuoteStatus $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }

    /**
     * Determine whether the user can import quote statuses.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import quote status');
    }

    /**
     * Determine whether the user can export quote statuses.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export quote status');
    }
}
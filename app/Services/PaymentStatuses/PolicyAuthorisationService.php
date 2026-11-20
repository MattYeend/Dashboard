<?php

declare(strict_types=1);

namespace App\Services\PaymentStatuses;

use App\Models\PaymentStatus;
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
     * Determine whether the user can view any payment statuses.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view payment statuses');
    }

    /**
     * Determine whether the user can create payment statuses.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create payment statuses');
    }

    /**
     * Determine whether the user can view the payment status.
     */
    public function canView(User $actor, PaymentStatus $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('view payment statuses')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the payment status.
     */
    public function canUpdate(User $actor, PaymentStatus $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('edit payment statuses')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the payment status.
     *
     * System statuses can never be deleted.
     */
    public function canDelete(User $actor, PaymentStatus $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('delete payment statuses')
            && $this->activeChecker->canBeDeleted($target);
    }

    /**
     * Determine whether the user can restore the payment status.
     */
    public function canRestore(User $actor, PaymentStatus $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('restore payment statuses')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the payment status.
     */
    public function canForceDelete(User $actor, PaymentStatus $target): bool
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
     * Determine whether the user can import payment statuses.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import payment statuses');
    }

    /**
     * Determine whether the user can export payment statuses.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export payment statuses');
    }
}

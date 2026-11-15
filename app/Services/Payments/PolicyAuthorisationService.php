<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Payment;
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
     * Determine whether the user can view any payments.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any payment');
    }

    /**
     * Determine whether the user can record payments.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create payment');
    }

    /**
     * Determine whether the user can view the payment.
     */
    public function canView(User $actor, Payment $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('view payment')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the payment. Completed
     * payments and refunds are locked.
     */
    public function canUpdate(User $actor, Payment $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('edit payment')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can delete the payment. Completed
     * payments and refunds are locked.
     */
    public function canDelete(User $actor, Payment $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('delete payment')
            && $this->activeChecker->canBeDeleted($target);
    }

    /**
     * Determine whether the user can restore the payment.
     */
    public function canRestore(User $actor, Payment $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('restore payment')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the payment.
     */
    public function canForceDelete(User $actor, Payment $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $this->activeChecker->canBeForceDeleted($target)
            && $this->activeChecker->canUserPerformAction(
                $actor,
                'restoreOrForceDelete',
                $target
            );
    }

    /**
     * Determine whether the user can refund the payment.
     */
    public function canRefund(User $actor, Payment $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('refund payment')
            && $this->activeChecker->canBeRefunded($target);
    }

    /**
     * Determine whether the user can import payments.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import payment');
    }

    /**
     * Determine whether the user can export payments.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export payment');
    }
}

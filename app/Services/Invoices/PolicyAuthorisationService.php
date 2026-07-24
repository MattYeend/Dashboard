<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
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
     * Check if invoice is active (not soft-deleted).
     */
    public function isActive(Invoice $invoice): bool
    {
        return $this->activeChecker->isActive($invoice);
    }

    /**
     * Check if invoice is soft-deleted.
     */
    public function isTrashed(Invoice $invoice): bool
    {
        return $this->activeChecker->isTrashed($invoice);
    }

    /**
     * Determine whether the user can view any invoices.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any invoice');
    }

    /**
     * Determine whether the user can create invoices.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create invoice');
    }

    /**
     * Determine whether the user can view the invoice.
     */
    public function canView(User $actor, Invoice $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view invoice') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the invoice.
     */
    public function canUpdate(User $actor, Invoice $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit invoice') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the invoice.
     */
    public function canDelete(User $actor, Invoice $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete invoice') && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the invoice.
     */
    public function canRestore(User $actor, Invoice $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore invoice') && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the invoice.
     */
    public function canForceDelete(User $actor, Invoice $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }

    /**
     * Determine whether the invoice was created by a user who outranks the actor.
     *
     * Prevents admins from managing tasks created by super admins.
     */
    private function targetOutranksActor(User $actor, Invoice $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $creator = $target->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($creator);
    }

    /**
     * Determine whether the user can send the invoice.
     */
    public function canSend(User $actor, Invoice $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('send invoices') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can mark the invoice as paid.
     */
    public function canMarkAsPaid(User $actor, Invoice $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('mark invoices as paid') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can mark the invoice as unpaid.
     */
    public function canMarkAsUnpaid(User $actor, Invoice $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('mark invoice as unpaid') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can change the invoice's status.
     */
    public function canChangeStatus(User $actor, Invoice $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('change invoice status') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can import invoices.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import invoice');
    }

    /**
     * Determine whether the user can export invoices.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export invoice');
    }

    /**
     * Determine whether the user can assign the invoice to another user.
     */
    public function canAssign(User $actor, Invoice $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('assign invoice') && $this->activeChecker->isActive($target);
    }
}

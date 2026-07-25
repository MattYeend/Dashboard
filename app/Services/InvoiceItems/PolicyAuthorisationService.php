<?php

namespace App\Services\InvoiceItems;

use App\Models\InvoiceItem;
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
     * Check if invoice item is active (not soft-deleted).
     */
    public function isActive(InvoiceItem $invoiceItem): bool
    {
        return $this->activeChecker->isActive($invoiceItem);
    }

    /**
     * Check if invoice item is soft-deleted.
     */
    public function isTrashed(InvoiceItem $invoiceItem): bool
    {
        return $this->activeChecker->isTrashed($invoiceItem);
    }

    /**
     * Determine whether the user can view any invoice items.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any invoice item');
    }

    /**
     * Determine whether the user can create invoice items.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create invoice item');
    }

    /**
     * Determine whether the user can view the invoice item.
     */
    public function canView(User $actor, InvoiceItem $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view invoice item') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the invoice item.
     */
    public function canUpdate(User $actor, InvoiceItem $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit invoice item') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the invoice item.
     */
    public function canDelete(User $actor, InvoiceItem $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete invoice item') && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the invoice item.
     */
    public function canRestore(User $actor, InvoiceItem $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore invoice item') && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the invoice item.
     */
    public function canForceDelete(User $actor, InvoiceItem $target): bool
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
     * Determine whether the user can import invoice items.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import invoice item');
    }

    /**
     * Determine whether the user can export invoice items.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export invoice item');
    }

    /**
     * Determine whether the invoice item's parent invoice was created by a
     * user who outranks the actor.
     *
     * Prevents admins from managing invoice items belonging to invoices
     * created by super admins.
     */
    private function targetOutranksActor(User $actor, InvoiceItem $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $creator = $target->invoice?->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($creator);
    }
}

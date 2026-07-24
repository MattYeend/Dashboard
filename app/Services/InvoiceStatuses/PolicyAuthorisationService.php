<?php

namespace App\Services\InvoiceStatuses;

use App\Models\InvoiceStatus;
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
    public function isUser(
        User $user
    ): bool {
        return $this->roleChecker->isUser(
            $user
        );
    }

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(
        User $user
    ): bool {
        return $this->roleChecker->isAdmin(
            $user
        );
    }

    /**
     * Check if invoiceStatus is active (not soft-deleted).
     */
    public function isActive(
        InvoiceStatus $invoiceStatus
    ): bool {
        return $this->activeChecker->isActive(
            $invoiceStatus
        );
    }

    /**
     * Check if invoiceStatus is soft-deleted.
     */
    public function isTrashed(
        InvoiceStatus $invoiceStatus
    ): bool {
        return $this->activeChecker->isTrashed(
            $invoiceStatus
        );
    }

    /**
     * Determine whether the user can view the task status.
     */
    public function canView(
        User $actor,
        InvoiceStatus $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $this->isAdmin($actor)
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the task status.
     */
    public function canUpdate(
        User $actor,
        InvoiceStatus $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $this->isAdmin($actor)
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the task status.
     */
    public function canDelete(
        User $actor,
        InvoiceStatus $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $this->isAdmin($actor)
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the task status.
     */
    public function canRestore(
        User $actor,
        InvoiceStatus $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $this->isAdmin($actor)
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the task status.
     */
    public function canForceDelete(
        User $actor,
        InvoiceStatus $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }

    /**
     * Determine whether the task status was created by a user who outranks the actor.
     *
     * Prevents admins from managing task statuses created by super admins.
     */
    private function targetOutranksActor(
        User $actor,
        InvoiceStatus $target
    ): bool {
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
     * Determine whether the user can import invoice statuses.
     */
    public function canImport(
        User $actor
    ): bool {
        return $this->isAdmin(
            $actor
        );
    }

    /**
     * Determine whether the user can export invoice statuses.
     */
    public function canExport(
        User $actor
    ): bool {
        return $this->isUser(
            $actor
        );
    }

    /**
     * Determine whether the user can assign the invoice status.
     */
    public function canAssign(
        User $actor,
        InvoiceStatus $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $actor->can('assign invoice statuses')
            && $this->activeChecker->isActive($target);
    }
}

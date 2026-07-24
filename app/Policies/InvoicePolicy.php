<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Services\Invoices\PolicyAuthorisationService;

class InvoicePolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any invoices.
     */
    public function viewAny(
        User $user
    ): bool {
        return $this->authorisationService->isAdmin(
            $user
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(
        User $user,
        Invoice $invoice
    ): bool {
        return $this->authorisationService->canView(
            $user,
            $invoice
        );
    }

    /**
     * Determine whether the user can create invoices.
     */
    public function create(
        User $user
    ): bool {
        return $this->authorisationService->isAdmin(
            $user
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(
        User $user,
        Invoice $invoice
    ): bool {
        return $this->authorisationService->canUpdate(
            $user,
            $invoice
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(
        User $user,
        Invoice $invoice
    ): bool {
        return $this->authorisationService->canDelete(
            $user,
            $invoice
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(
        User $user,
        Invoice $invoice
    ): bool {
        return $this->authorisationService->canRestore(
            $user,
            $invoice
        );
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(
        User $user,
        Invoice $invoice
    ): bool {
        return $this->authorisationService->canForceDelete(
            $user,
            $invoice
        );
    }

    /**
     * Determine whether the user can send the model.
     */
    public function send(
        User $user,
        Invoice $invoice
    ): bool {
        return $this->authorisationService->canSend($user, $invoice);
    }

    /**
     * Determine whether the user can mark the model as paid.
     */
    public function markAsPaid(
        User $user,
        Invoice $invoice
    ): bool {
        return $this->authorisationService->canMarkAsPaid(
            $user,
            $invoice
        );
    }

    /**
     * Determine whether the user can mark the model as unpaid.
     */
    public function markAsUnpaid(
        User $user,
        Invoice $invoice
    ): bool {
        return $this->authorisationService->canMarkAsUnpaid(
            $user,
            $invoice
        );
    }

    /**
     * Determine whether the user can import invoices.
     */
    public function import(
        User $user
    ): bool {
        return $this->authorisationService->canImport(
            $user
        );
    }

    /**
     * Determine whether the user can export invoices.
     */
    public function export(
        User $user
    ): bool {
        return $this->authorisationService->canExport(
            $user
        );
    }

    /**
     * Determine whether the user can assign the invoice to another user.
     */
    public function assign(
        User $user,
        Invoice $invoice
    ): bool {
        return $this->authorisationService->canAssign(
            $user,
            $invoice
        );
    }
}

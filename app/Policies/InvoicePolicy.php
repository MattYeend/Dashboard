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
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can view the invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return $this->authorisationService->canView($user, $invoice);
    }

    /**
     * Determine whether the user can create invoices.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can update the invoice.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        return $this->authorisationService->canUpdate($user, $invoice);
    }

    /**
     * Determine whether the user can delete the invoice.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->authorisationService->canDelete($user, $invoice);
    }

    /**
     * Determine whether the user can restore the invoice.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        return $this->authorisationService->canRestore($user, $invoice);
    }

    /**
     * Determine whether the user can permanently delete the invoice.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return $this->authorisationService->canForceDelete($user, $invoice);
    }

    /**
     * Determine whether the user can send the invoice.
     */
    public function send(User $user, Invoice $invoice): bool
    {
        return $this->authorisationService->canSend($user, $invoice);
    }

    /**
     * Determine whether the user can mark the invoice as paid.
     */
    public function markAsPaid(User $user, Invoice $invoice): bool
    {
        return $this->authorisationService->canMarkAsPaid($user, $invoice);
    }

    /**
     * Determine whether the user can mark the invoice as unpaid.
     */
    public function markAsUnpaid(User $user, Invoice $invoice): bool
    {
        return $this->authorisationService->canMarkAsUnpaid($user, $invoice);
    }
}

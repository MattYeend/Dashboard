<?php

namespace App\Policies;

use App\Models\TicketStatus;
use App\Models\User;
use App\Services\TicketStatuses\PolicyAuthorisationService;

class TicketStatusPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TicketStatus $ticketStatus): bool
    {
        return $this->authorisationService->canView($user, $ticketStatus);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->canCreate($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TicketStatus $ticketStatus): bool
    {
        return $this->authorisationService->canUpdate($user, $ticketStatus);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TicketStatus $ticketStatus): bool
    {
        return $this->authorisationService->canDelete($user, $ticketStatus);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TicketStatus $ticketStatus): bool
    {
        return $this->authorisationService->canRestore($user, $ticketStatus);
    }

    /**
     * Determine whether the user can permanently delete the models.
     */
    public function forceDelete(User $user, TicketStatus $ticketStatus): bool
    {
        return $this->authorisationService->canForceDelete($user, $ticketStatus);
    }

    /**
     * Determine whether the user can assign the task status.
     */
    public function assign(User $user, TicketStatus $ticketStatus): bool
    {
        return $this->authorisationService->canAssign($user, $ticketStatus);
    }

    /**
     * Determine whether the user can import models.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export models.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }
}

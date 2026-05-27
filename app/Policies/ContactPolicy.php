<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use App\Services\Contacts\PolicyAuthorisationService;

class ContactPolicy
{
    /**
     * The authorisation service handling permission checks.
     *
     * @var PolicyAuthorisationService
     */
    protected PolicyAuthorisationService $authorisationService;

    /**
     * Inject the required service into the policy.
     *
     * @param  PolicyAuthorisationService $authorisationService
     */
    public function __construct(
        PolicyAuthorisationService $authorisationService
    ) {
    }

    /**
     * Determine whether the user can view any models.
     *
     * Only admins can view the list of companies.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User$user
     * @param  Contact $contact
     *
     * @return bool
     */
    public function view(User $user, Contact $contact): bool
    {
        return $this->authorisationService->canView($user, $contact);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User $user
     * @param  Contact $contact
     *
     * @return bool
     */
    public function update(User $user, Contact $contact): bool
    {
        return $this->authorisationService->canUpdate($user, $contact);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User $user
     * @param  Contact $contact
     *
     * @return bool
     */
    public function delete(User $user, Contact $contact): bool
    {
        return $this->authorisationService->canDelete($user, $contact);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User $user
     * @param  Contact $contact
     *
     * @return bool
     */
    public function restore(User $user, Contact $contact): bool
    {
        return $this->authorisationService->canRestore($user, $contact);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  User $user
     * @param  Contact $contact
     *
     * @return bool
     */
    public function forceDelete(User $user, Contact $contact): bool
    {
        return $this->authorisationService->canForceDelete(
            $user,
            $contact
        );
    }

    /**
     * Determine whether the user can bulk delete models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function bulkDelete(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can bulk restore models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function bulkRestore(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can import models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can export models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->isUser($user);
    }
}

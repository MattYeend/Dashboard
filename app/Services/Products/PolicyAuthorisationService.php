<?php

declare(strict_types=1);

namespace App\Services\Products;

use App\Models\Product;
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
     * Determine whether the user can view any products.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any product');
    }

    /**
     * Determine whether the user can create products.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create product');
    }

    /**
     * Determine whether the user can view the product.
     */
    public function canView(User $actor, Product $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('view product')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the product.
     */
    public function canUpdate(User $actor, Product $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('edit product')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the product.
     */
    public function canDelete(User $actor, Product $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('delete product')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the product.
     */
    public function canRestore(User $actor, Product $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('restore product')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the product.
     */
    public function canForceDelete(User $actor, Product $target): bool
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
     * Determine whether the user can import products.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import product');
    }

    /**
     * Determine whether the user can export products.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export product');
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Products;

use App\Models\Product;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class ActiveCheckerService
{
    /**
     * Inject the required services into the active checker service.
     */
    public function __construct(
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if product is active (not soft-deleted).
     */
    public function isActive(Product $product): bool
    {
        return ! $product->trashed();
    }

    /**
     * Check if product is soft-deleted.
     */
    public function isTrashed(Product $product): bool
    {
        return $product->trashed();
    }

    /**
     * Check if product is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(Product $product): bool
    {
        return $this->isActive($product);
    }

    /**
     * Check if product is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        Product $product
    ): bool {
        return $this->isTrashed($product);
    }

    /**
     * Check if user can modify product (update/delete) or restore/force-delete
     * product based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Product $target,
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

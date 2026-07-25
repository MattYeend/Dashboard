<?php

namespace App\Services\Categories;

use App\Models\Category;
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
     * Check if category is active (not soft-deleted).
     */
    public function isActive(
        Category $category
    ): bool {
        return ! $category->trashed();
    }

    /**
     * Check if category is soft-deleted.
     */
    public function isTrashed(
        Category $category
    ): bool {
        return $category->trashed();
    }

    /**
     * Check if category is active and can be updated or deleted.
     */
    public function canBeModified(
        Category $category
    ): bool {
        return $this->isActive(
            $category
        );
    }

    /**
     * Check if category is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        Category $category
    ): bool {
        return $this->isTrashed(
            $category
        );
    }

    /**
     * Check if the user can perform an action on the category.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Category $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}

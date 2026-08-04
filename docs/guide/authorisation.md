---
title: Authorisation
# group: Guides              # Bucket this page sits under in the sidebar
order: 5
# description: A short summary used for <meta> tags and SEO
# slug: custom-url           # Override the URL slug (defaults to the file path)
# hidden: true               # Hide from the sidebar and listings
# badge: New                 # Small label shown next to the title in the sidebar
# icon: book                 # Icon name (consumed by your views/macros)
# tags: [intro, basics]      # Free-form tags
# updated_at: 2026-01-01     # Shown in the page footer when set
# author: Jane Doe
# layout: docs               # Override the Blade layout used to render this page
# image: /img/social.png     # Social/OG image
# redirect: /docs/other      # Permanent redirect to another URL
---

# Authorisation

Built on Spatie `laravel-permission`, with a project-specific layer on top to handle role ranking.

## Roles

Standard roles: `user`, `admin`, `super_admin` (see the `role` field on `User` in [Frontend Types](08-frontend-types.md)). Roles and permissions are seeded via `RolePermissionsSeeder`. **Any new module must update this seeder** with its permissions (see [CLI Reference](09-cli-reference.md) for the expected format).

## Policy pattern

Policies stay thin and delegate to `PolicyAuthorisationService`:

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use App\Services\Posts\PolicyAuthorisationService;

class PostPolicy
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
    public function view(User $user, Post $post): bool
    {
        return $this->authorisationService->canView($user, $post);
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
    public function update(User $user, Post $post): bool
    {
        return $this->authorisationService->canUpdate($user, $post);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        return $this->authorisationService->canDelete($user, $post);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        return $this->authorisationService->canRestore($user, $post);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $this->authorisationService->canForceDelete($user, $post);
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
```

## `PolicyAuthorisationService` responsibilities

The service backs every ability declared on the Policy (`canViewAny`, `canView`, `canCreate`, `canUpdate`, `canDelete`, `canRestore`, `canForceDelete`, `canImport`, `canExport`), but the two rules below apply consistently across all of them:

1. Delegates the base "does this role have this permission at all" check to `UserRoleCheckerService`.
2. Applies `targetOutranksActor($actor, $target)`: a lower-ranked user cannot modify, delete, restore, or force-delete a record created by a higher-ranked user, even where their role would otherwise permit the action. This protects, for example, an `admin`-created record from being deleted by a lower-permissioned `user` who happens to hold a delete permission on the resource type generally.

## `UserRoleCheckerService`

Centralises "what can this role do" so that rank comparisons aren't duplicated per-module. Typical methods:

- `hasRole(User $user, string $role): bool`
- `outranks(User $a, User $b): bool`
- `rankOf(string $role): int`

## Frontend permission flags

Controllers pass a `PermissionsMeta`-shaped object (`can_create`, `can_view_any`, etc.) to Inertia so the frontend can conditionally render create/edit/delete affordances without re-implementing the authorisation logic in Vue. The frontend flag is a **UI convenience only**; the Policy is still the source of truth and is checked server-side on every request.
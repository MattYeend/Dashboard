---
title: Routes
# group: Guides              # Bucket this page sits under in the sidebar
order: 6
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

# Routes

## Grouping convention

Every resource gets its own `prefix`/`name` group in `routes/web.php`. Bulk and trash-related routes are listed first, then the standard RESTful set:

```php
Route::prefix('users')->name('users.')->group(function () {
    Route::post('/bulk/delete', [UserController::class, 'bulkDelete'])->name('bulk.delete');
    Route::post('/bulk/restore', [UserController::class, 'bulkRestore'])->name('bulk.restore');
    Route::post('/{id}/restore', [UserController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force', [UserController::class, 'forceDelete'])->name('force-delete');

    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::patch('/{user}', [UserController::class, 'update'])->name('patch');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});
```

## Notes

- `/{id}/restore` and `/bulk/*` use a raw `{id}` rather than route-model binding, because the target record is soft-deleted (not resolvable via the default binding, which excludes trashed records).
- `/{user}` (implicit binding) is used everywhere the record is expected to be non-trashed.
- Both `PUT` and `PATCH` are registered against `update`, since some frontend tooling and third-party integrations only support one or the other.
- `force-delete` is a distinct, more destructive route from `destroy` (soft delete) and `bulk.delete`. It permanently removes the record and should be gated behind a stricter Policy check (typically `super_admin` only).

## Frontend consumption

Routes are never referenced with the Ziggy `route()` helper. Instead, Laravel Wayfinder generates typed route helpers per resource:

```ts
import { index as usersIndex, store as usersStore } from '@/routes/users';
import { bulkDelete as usersBulkDelete } from '@/routes/users/bulk';
```

This gives compile-time checking of route parameters and avoids stringly-typed route names in Vue components.
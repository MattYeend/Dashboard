---
title: Service Layer
# group: Guides              # Bucket this page sits under in the sidebar
order: 2
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

# Service Layer

Every module has the same set of single-responsibility service classes. This keeps controllers thin and keeps business logic testable and consistent across modules.

## Scaffolding a service

```bash
php artisan make:service {PluralModelName}/{ServiceName}
```

The folder name is always **plural**; there is no singular-named service folder.

Example, for a `Post` module:

```bash
php artisan make:service Posts/CreatorService
php artisan make:service Posts/UpdaterService
php artisan make:service Posts/DeleterService
php artisan make:service Posts/RestorerService
php artisan make:service Posts/QueryService
php artisan make:service Posts/FilterService
php artisan make:service Posts/SortingService
php artisan make:service Posts/FormatterService
php artisan make:service Posts/DataPreparationService
php artisan make:service Posts/ManagementService
php artisan make:service Posts/ActiveCheckerService
php artisan make:service Posts/PolicyAuthorisationService
```

## The standard service set

| Service | Responsibility |
|---|---|
| `ActiveCheckerService` | Checks whether a record (or a related record) is active/enabled before allowing an operation |
| `CreatorService` | Handles creation: validates input shape, calls `CreateResource` action, triggers audit log entry |
| `DataPreparationService` | Prepares/normalises data before it's persisted (e.g. slug generation via `SlugService`, casting, defaulting nullable fields) |
| `DeleterService` | Handles soft-delete: calls `DeleteResource` action, triggers audit log entry |
| `FilterService` | Applies search/filter query constraints; uses the `EscapesLikeValues` trait to safely escape `LIKE` wildcards in user input |
| `FormatterService` | Formats data for output (e.g. shaping resource data, date formatting, relation shaping for the frontend) |
| `ManagementService` | Thin orchestration layer: the entry point controllers call; delegates to Creator/Updater/Deleter/Restorer |
| `PolicyAuthorisationService` | Called from the model's Policy; delegates role checks to `UserRoleCheckerService` and blocks actions where the target record was created by a higher-ranked user (`targetOutranksActor`) |
| `QueryService` | Builds the base query for index/listing pages; injects `SortingService`, `TrashFilterService`, `FilterService`, `FormatterService` |
| `RestorerService` | Handles restoring a soft-deleted record: calls `RestoreResource` action, triggers audit log entry |
| `SortingService` | Applies `sort_by` / `sort_direction` query constraints |
| `UpdaterService` | Handles updates: calls `UpdateResource` action, triggers audit log entry |

> **Note:** `TrashFilterService` is a shared, cross-cutting service (trashed/with-trashed/only-trashed query scoping). It is **not** part of the standard per-module service set, but `QueryService` injects it.

## Example flow: updating a record

```
UserController::update()
    → UserManagementService::update()
        → UserUpdaterService::update()
            → DataPreparationService::prepare()   (normalise/clean input)
            → UpdateResource::execute()            (shared Action, does the actual save + transaction)
            → AuditLogService::log()               (records before/after snapshot)
```

## Policy authorisation pattern

`PolicyAuthorisationService` is injected into the model's Policy class rather than putting role logic directly in the Policy. This keeps Policies thin, delegating each ability check to the service. See [Authorisation](05-authorisation.md) for a complete `PostPolicy` example.

`PolicyAuthorisationService` in turn delegates the "is this role allowed at all" question to `UserRoleCheckerService`, and separately checks `targetOutranksActor($user, $post)`, i.e. it blocks a lower-ranked user from modifying a record created by a higher-ranked user, even if their role would otherwise permit the action.
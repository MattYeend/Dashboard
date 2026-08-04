---
title: Actions
# group: Guides              # Bucket this page sits under in the sidebar
order: 3
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

# Actions

Shared `App\Actions` classes centralise the actual database work and transaction handling that would otherwise be duplicated across every module's Creator/Updater/Deleter/Restorer services.

## The four shared actions

| Action | Used by | Responsibility |
|---|---|---|
| `CreateResource` | `CreatorService` | Wraps `Model::create()` in a transaction, sets `created_by` |
| `UpdateResource` | `UpdaterService` | Wraps `$model->update()` in a transaction, sets `updated_by` |
| `DeleteResource` | `DeleterService` | Wraps soft delete in a transaction, sets `deleted_by` |
| `RestoreResource` | `RestorerService` | Wraps `$model->restore()` in a transaction, sets `restored_by` and `restored_at` |

These are constructor-injected into the corresponding service (e.g. `UserCreatorService` depends on `CreateResource`), so the service stays focused on business rules (validation-adjacent logic, audit logging, data preparation) while the Action stays focused purely on "how does this write actually happen safely".

## When to add a new Action class

Reach for a new `App\Actions` class (rather than putting the logic inline in a service) when:

- The logic wraps a **transaction** around more than one write (e.g. writing to two tables at once)
- The logic is **reused across multiple modules** unmodified
- The logic would otherwise make a service class do two distinct jobs (e.g. "prepare data" *and* "persist data")

## Scaffolding

Laravel doesn't have a first-class `make:action` command in this project; new Action classes are added manually under `app/Actions/`, following the existing naming pattern (verb + `Resource`, e.g. `CreateResource`, or a more specific name where the action is domain-specific, e.g. `AttachLikeToPost`).

```bash
php artisan make:class Actions/AttachLikeToPost
```

(Or create the file directly under `app/Actions/` if `make:class` isn't available. It should be a plain invokable class, typically with a single `execute()` or `__invoke()` method.)

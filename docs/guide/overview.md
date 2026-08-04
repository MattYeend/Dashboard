---
title: Overview
# group: Guides              # Bucket this page sits under in the sidebar
order: 1
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

# Overview

## Stack
 
- **Backend**: Laravel 13, PHP
- **Frontend**: Vue.js 3 (`<script setup>`), TypeScript
- **Bridge**: Inertia.js (server-driven SPA, no separate API layer for the UI)
- **Database**: MySQL
- **Testing**: Pest
- **Routing helpers**: Laravel Wayfinder (typed route helpers), not the Ziggy `route()` helper
- **Authorisation**: Spatie `laravel-permission`
- **Rich text sanitisation**: `Mews\Purifier`
- **Billing**: Laravel Cashier (Stripe) (see [Plans Module](10-plans-module.md))

## Core principle

Every resource module (Users, Tasks, TaskStatuses, Orders, Contacts, Companies, Posts, Comments, Categories, Deals, Pipelines, etc.) follows the **same shape**. A developer who understands one module understands them all. This is deliberate: it keeps the codebase predictable, makes code review fast, and means a security or bug fix pattern found in one module can be mechanically checked against every other module.

## Anatomy of a module

Given a model `Post`, scaffolded with:

```bash
php artisan make:model Post -a
```

You get:

```
app/Models/Post.php
database/migrations/..._create_posts_table.php
database/factories/PostFactory.php
database/seeders/PostSeeder.php
app/Policies/PostPolicy.php
app/Http/Controllers/PostController.php   (resource controller)
app/Http/Requests/StorePostRequest.php
app/Http/Requests/UpdatePostRequest.php
app/Http/Requests/ImportPostRequest.php
```

On top of that scaffold, each module adds:
 
- A **service set** (see [Service Layer](02-service-layer.md))
- Shared **Action classes** injected into the services (see [Actions](03-actions.md))
- **Audit logging** wiring via the `Auditable` contract (see [Audit Logging](04-audit-logging.md))
- A **Policy** that delegates to `PolicyAuthorisationService` (see [Authorisation](05-authorisation.md))
- Grouped, RESTful-plus-bulk **routes** (see [Routes](06-routes.md))
- Vue pages (`Index`, `Create`, `Edit`, `Show`) built from small, reusable **components** (see [Frontend: Vue Patterns](07-frontend-vue-patterns.md))
- A model interface in `types/index.ts` (see [Frontend: TypeScript Types](08-frontend-types.md))

## Model conventions
 
- Models use the PHP attribute `#[Fillable([...])]` instead of the `$fillable` property, plus a full PHPDoc `@property`/`@property-read` block.
- Migrations carry consistent audit columns: `created_by`, `updated_by`, `deleted_by`, `restored_by`, `restored_at`, plus `SoftDeletes` and datetime casts.
- Models implement an `Auditable` contract with `auditSnapshot()`, typically returning `$this->only([...])` against a fixed attribute list.
- Casting is done via a `casts()` method, not the older `$casts` property.
See [Example Models](11-example-models.md) for the full, real `Company` and `User` models showing every one of these conventions together.

## Naming quick reference

| Concern | Convention |
|---|---|
| Service scaffold | `php artisan make:service {PluralModelName}/{ServiceName}` |
| Standard service set | ActiveCheckerService, CreatorService, DataPreparationService, DeleterService, FilterService, FormatterService, ManagementService, PolicyAuthorisationService, QueryService, RestorerService, SortingService, UpdaterService |
| Shared actions | `CreateResource`, `UpdateResource`, `DeleteResource`, `RestoreResource` |
| Route grouping | `Route::prefix('{resource}')->name('{resource}.')->group(...)` (see [Routes](06-routes.md)) |
| Prop casing | camelCase between PHP and Vue |

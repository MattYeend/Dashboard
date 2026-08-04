---
title: Readme
# group: Guides              # Bucket this page sits under in the sidebar
order: 0
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

# Dashboard: Laradocs
Internal architecture documentation for the Dashboard application (Laravel 13 + Vue.js, Inertia, TypeScript, Pest).
 
## Pages
 
1. [Overview](01-overview.md): stack, principles, module anatomy
2. [Service Layer](02-service-layer.md): the per-module service set and responsibilities
3. [Actions](03-actions.md): shared `App\Actions` classes
4. [Audit Logging](04-audit-logging.md): `AuditLogService`, `Log` model, snapshots
5. [Authorisation](05-authorisation.md): Policies, `PolicyAuthorisationService`, role ranking
6. [Routes](06-routes.md): route naming and grouping conventions
7. [Frontend: Vue Patterns](07-frontend-vue-patterns.md): component structure, shared components, forms
8. [Frontend: TypeScript Types](08-frontend-types.md): `types/index.ts` conventions
9. [CLI Reference](09-cli-reference.md): `make:model`, `make:service`, artisan usage
10. [Plans Module (Stripe/Cashier)](10-plans-module.md): subscriptions, webhooks
11. [Example Models (Reference)](11-example-models.md): full, real `Company` and `User` models

## How to use these docs

Each page describes an established convention, not aspirational design. When building a new module, follow the patterns here rather than reinventing them. Consistency across modules (Users, Tasks, TaskStatuses, Orders, Contacts, Companies, Posts, etc.) is the point of the service-oriented architecture.

If a convention changes, update the relevant page in the same pull request as the code change.

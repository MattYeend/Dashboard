# Dashboard: Laradocs

Internal architecture documentation for the Dashboard application (Laravel 13 + Vue.js, Inertia, TypeScript, Pest).

## Pages

1. [Overview](overview.md): stack, principles, module anatomy
2. [Service Layer](service-layer.md): the per-module service set and responsibilities
3. [Actions](actions.md): shared `App\Actions` classes
4. [Audit Logging](audit-logging.md): `AuditLogService`, `Log` model, snapshots
5. [Authorisation](authorisation.md): Policies, `PolicyAuthorisationService`, role ranking
6. [Routes](routes.md): route naming and grouping conventions
7. [Frontend: Vue Patterns](frontend-vue-patterns.md): component structure, shared components, forms
8. [Frontend: TypeScript Types](frontend-types.md): `types/index.ts` conventions
9. [CLI Reference](cli-reference.md): `make:model`, `make:service`, artisan usage
10. [Plans Module (Stripe/Cashier)](plans-module.md): subscriptions, webhooks
11. [Example Models (Reference)](example-models.md): full, real `Company` and `User` models

## How to use these docs

Each page describes an established convention, not aspirational design. When building a new module, follow the patterns here rather than reinventing them. Consistency across modules (Users, Tasks, TaskStatuses, Orders, Contacts, Companies, Posts, etc.) is the point of the service-oriented architecture.

If a convention changes, update the relevant page in the same pull request as the code change.
# Contributing

## Workflow

1. Create a branch: `feature/{issue-number}-{short-name}`.
2. Make small commits: `git commit -m "#{issue} {Message}"`.
3. Run the local checks (below) and open a pull request.

## Local checks

```bash
composer run lint:check
composer run analyse
composer run test
npm run lint:check
npm run types:check
npm run build
```

## Standards

- PSR-12 and Laravel Pint for PHP; ESLint, Prettier and strict TypeScript for the frontend.
- Controllers stay thin and never touch the `DB` facade.
- Every new model is scaffolded with `php artisan make:model {Model} -a`.
- Every new model needs a policy, permissions in `RolePermissionSeeder` and audit logging.
- Service classes follow the standard naming set, scaffolded with `php artisan make:service {Model}/{ServiceName}`:
  - `ActiveCheckerService`, `CreatorService`, `DataPreparationService`, `DeleterService`, `FilterService`, `FormatterService`, `ManagementService`, `PolicyAuthorisationService`, `QueryService`, `RestorerService`, `SortingService`, `UpdaterService`
- Import requests use `php artisan make:request Import{Model}Request`.
- Seeders use real reference data, never `fake()`.
- Vue: split forms and show pages into small components under `{Model}/components/`, and do not add background colour classes.
- Web routes follow the standard grouped/prefixed pattern already used across the app (see `routes/web.php`).
- `ManagementService::bulkDelete()`/`bulkRestore()` must preserve the caller's requested id order in the `deleted`/`restored`/`skipped` arrays (see `App\Services\Posts\ManagementService` as the canonical example) — look models up by id with `->get()->keyBy('id')` and iterate the requested ids, rather than `->diff()` against query results, so tests can assert exact array equality instead of a canonicalised comparison.

## Tenant scoping (organisation_id)

Add this checklist to every new tenant-scoped module:

- Migration has `organisation_id` (foreign key, indexed, composite index with the most common filter, e.g. `[organisation_id, created_at]`).
- Model uses `App\Traits\BelongsToOrganisation` and is added to `config('organisations.scoped_models')`.
- `organisation_id` is not in the model's `#[Fillable([...])]` list.
- Factory creates its own parent rows.
- Resource is added to `tenantResourceMap()` (or `tenantBulkOnlyResourceMap()`) in `tests/Feature/Tenancy/TenantHttpIsolationTest.php`.
- Any file storage path is prefixed with `organisations/{id}/`.
- Any cache key includes the organisation id (the tenant cache prefix task handles framework calls).
- Any queued job class is tenant aware (the default in this project).
- If the table is deliberately not organisation-scoped, it is added to `config('organisations.central_tables')` with a one-line reason.
- Any `withoutGlobalScope(s)` call is added to `config('organisations.global_scope_bypass_allow_list')` with a one-line reason, or it is not added at all and the code is changed instead. Before adding a bypass at all, confirm the target model is actually scoped by `BelongsToOrganisation` — a `withoutGlobalScope` call against a central table (see `ReportingService`, #440) is dead code, not a bypass, and should simply be deleted.

## Security

Never commit secrets. Report vulnerabilities through SECURITY.md, not public issues.

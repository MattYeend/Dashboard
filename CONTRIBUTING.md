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
- Every new table needs `organisation_id` unless it is listed as central in `config/organisations.php`.
- Seeders use real reference data, never `fake()`.
- Vue: split forms and show pages into small components under `{Model}/components/`, and do not add background colour classes.
- Web routes follow the standard grouped/prefixed pattern already used across the app (see `routes/web.php`).

## Security

Never commit secrets. Report vulnerabilities through SECURITY.md, not public issues.
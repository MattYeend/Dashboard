# Dashboard

A modern admin dashboard built with **Laravel 13**, **Vue 3**, **TypeScript**, and **Inertia.js**. It provides a clean, responsive interface with role-based access control, authentication via Laravel Fortify and Passkeys, and a fully typed frontend powered by Vite and Tailwind CSS v4.

---

## Table of Contents

- [Requirements](#requirements)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Installation](#installation)
- [Roles and Permissions](#roles-and-permissions)
- [Configuration](#configuration)
- [Running the Application](#running-the-application)
- [Testing](#testing)
- [Code Quality](#code-quality)
- [Scripts Reference](#scripts-reference)
- [Generated Frontend Routes](#generated-frontend-routes)
- [Local Quality Checks](#local-quality-checks)
- [Further Reading](#further-reading)
- [Security](#security)
- [Contributing](#contributing)
- [Licence](#licence)
- [Funding](#funding)

---

## Requirements

- PHP 8.3 or higher
- Composer
- Node.js 20 or higher
- npm
- A supported database: MySQL 8 or PostgreSQL 16 (both run in CI). SQLite is for local development only

---

## Tech Stack

**Backend**

- [Laravel 13](https://laravel.com) -- PHP framework
- [Laravel Fortify](https://laravel.com/docs/fortify) -- Authentication backend
- [Laravel Sanctum](https://laravel.com/docs/sanctum) -- API token authentication
- [Laravel Wayfinder](https://github.com/laravel/wayfinder) -- Named route generation for TypeScript
- [Spatie Laravel Permission v7](https://spatie.be/docs/laravel-permission) -- Role and permission management
- [Pest PHP](https://pestphp.com) -- Testing framework

**Frontend**

- [Vue 3](https://vuejs.org) with Composition API
- [TypeScript](https://www.typescriptlang.org)
- [Inertia.js](https://inertiajs.com) -- Server-driven SPA
- [Tailwind CSS v4](https://tailwindcss.com)
- [Reka UI](https://reka-ui.com) -- Headless UI components
- [Lucide Vue Next](https://lucide.dev) -- Icon library
- [Vite 8](https://vitejs.dev) -- Frontend build tool

---

## Architecture

Each resource follows the same service-oriented layout:

| Class | Responsibility |
| --- | --- |
| `ActiveCheckerService` | Whether a record can be modified, restored or force deleted |
| `CreatorService` / `UpdaterService` | Create and update, stamping audit columns |
| `DeleterService` / `RestorerService` | Soft delete and restore |
| `DataPreparationService` | Normalise validated input before persistence |
| `FilterService` / `SortingService` | Allow-listed filters and sort columns |
| `QueryService` | Build and paginate the index query |
| `FormatterService` | Shape data for Inertia props |
| `PolicyAuthorisationService` | Permission checks used by policies |
| `ManagementService` | Orchestrates the above for controllers |

Shared `App\Actions\*Resource` classes wrap transactions. Every state change is written to the audit log through `AuditLogService`.

Scaffold a new module with:

```bash
php artisan make:model {Model} -a
php artisan make:request Import{Model}Request
php artisan make:service {Model}/{ServiceName}
```

---

## Installation

Clone the repository and install dependencies:

```bash
git clone https://github.com/MattYeend/Dashboard.git
cd Dashboard
```

You can run the full setup in a single command:

```bash
composer run setup
```

This will:

1. Install Composer dependencies
2. Copy `.env.example` to `.env` (if not already present)
3. Generate an application key
4. Run database migrations and seed roles, permissions and reference data
5. Install npm dependencies
6. Build frontend assets

Alternatively, run each step manually:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

---

## Roles and Permissions

This project uses [Spatie Laravel Permission v7](https://spatie.be/docs/laravel-permission/v7/installation-laravel) to manage roles and permissions. It supports assigning roles and permissions directly to users, teams-based permissions, wildcard permissions, and Blade directives for view-level access control.

For full usage documentation, refer to the [Spatie Laravel Permission v7 docs](https://spatie.be/docs/laravel-permission/v7/basic-usage/basic-usage).

---

## Configuration

Copy the example environment file and update it with your own values:

```bash
cp .env.example .env
```

Key variables to configure:

| Variable | Description |
|---|---|
| `APP_NAME` | The name displayed in the application |
| `APP_URL` | The base URL of your application |
| `DB_CONNECTION` | Database driver (`mysql`, `pgsql`, `sqlite`) |
| `DB_HOST` | Database host |
| `DB_DATABASE` | Database name |
| `DB_USERNAME` | Database username |
| `DB_PASSWORD` | Database password |

---

## Running the Application

To start all services concurrently (web server, queue worker, log watcher, and Vite dev server):

```bash
composer run dev
```

This runs the following in parallel:

- `php artisan serve` -- Laravel development server
- `php artisan queue:listen` -- Queue worker
- `php artisan pail` -- Log viewer
- `npm run dev` -- Vite HMR dev server

To build frontend assets for production:

```bash
npm run build
```

---

## Testing

Tests are written using [Pest PHP](https://pestphp.com).

Run the full test suite:

```bash
php artisan test
```

Or via Composer:

```bash
composer run test
```

This also clears the config cache and runs the linter before executing tests.

---

## Code Quality

The project uses several tools to maintain code quality and consistency.

**PHP linting with Pint:**

```bash
# Fix violations
composer run lint

# Check only (no fixes applied)
composer run lint:check
```

**Frontend linting with ESLint:**

```bash
# Fix violations
npm run lint

# Check only
npm run lint:check
```

**Code formatting with Prettier:**

```bash
# Format resources/
npm run format

# Check only
npm run format:check
```

**TypeScript type checking:**

```bash
npm run types:check
```

---

## Scripts Reference

| Command | Description |
|---|---|
| `composer run setup` | Full project setup |
| `composer run dev` | Start all dev services |
| `npm run dev` | Start Vite dev server |
| `npm run build` | Build frontend for production |
| `npm run lint` | Fix ESLint violations |
| `npm run lint:check` | Check ESLint violations (no fixes applied) |
| `npm run format` | Format with Prettier |
| `npm run format:check` | Check formatting (no fixes applied) |
| `npm run types:check` | Check TypeScript types |
| `php artisan test` | Run the full Pest PHP test suite |
| `php artisan make:service {Model}/{ServiceName}` | Scaffold a service, for example `Companies/CreatorService` |
| `composer run analyse` | Run Larastan static analysis |
| `composer audit` | Check PHP dependencies for known vulnerabilities |
| `npm audit` | Check JavaScript dependencies for known vulnerabilities |

---

## Generated Frontend Routes

After changing Laravel routes or controllers, regenerate frontend route/action files:

```bash
php artisan wayfinder:generate --with-form
npm run types:check
```

Generated files live in:

- `resources/js/actions`
- `resources/js/routes`

---

## Local Quality Checks

Run before opening a PR:

```bash
composer test
npm run lint:check
npm run types:check
npm run build
```

---

## Further Reading

More artisan commands can be found <a href="https://artisan.page/" target="_blank">here</a>

---

## Security

Report vulnerabilities privately as described in [SECURITY.md](SECURITY.md). Do not open public issues for security problems.

---

## Contributing

Contributions are welcome. Please open an issue or submit a pull request. Ensure all tests pass and code style checks succeed before submitting.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/my-feature`)
3. Commit your changes (`git commit -m 'Add my feature'`)
4. Push to the branch (`git push origin feature/my-feature`)
5. Open a Pull Request

---

## Licence

This project is licenced under the [MIT Licence](LICENSE).

---

## Funding

If you find this project useful and would like to support its development, you can do so through the following:

- [Sponsor MattYeend on GitHub](https://github.com/sponsors/MattYeend)
- [Sponsor MatthewYeend on GitHub](https://github.com/sponsors/MatthewYeend)
- [Buy Me a Coffee](https://www.buymeacoffee.com/mattyeend)
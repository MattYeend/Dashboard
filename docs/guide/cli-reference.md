---
title: Cli Reference
# group: Guides              # Bucket this page sits under in the sidebar
order: 9
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

# CLI Reference

## New model + full scaffold

```bash
php artisan make:model Post -a
```

Generates:

```
app/Models/Post.php
Migration
Factory
Seeder
Policy
Resource Controller
Form Requests: ImportPostRequest, StorePostRequest, UpdatePostRequest
```

## Service classes

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

Folder name is always **plural**, singular service name; never `Post/CreatorService`.

## Seeders

Seeders always use **real, representative data**, never `fake()` or `$this->faker`. This keeps seeded data meaningful for manual QA and demos, not just structurally valid.

Any new module with permissions **must** update `RolePermissionsSeeder` with its permission set, e.g.:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionsSeeder extends Seeder
{
    /**
     * The permission set for each resource, keyed by resource name.
     */
    protected array $permissions = [
        'posts' => [
            'view any', 'view', 'create', 'update', 'delete', 'restore', 'force delete',
        ],
        // ...other resources
    ];

    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        foreach ($this->permissions as $resource => $abilities) {
            foreach ($abilities as $ability) {
                Permission::findOrCreate("{$resource} {$ability}");
            }
        }

        $admin = Role::findOrCreate('admin');
        $admin->givePermissionTo(Permission::all());
    }
}
```

## Tests

```bash
php artisan pest:test Posts/PostServiceTest
```

(Pest is the test runner across the project. New tests should follow existing `tests/Feature` / `tests/Unit` structure per module.)

## Documentation pages

New guide pages under `docs/` are scaffolded with a project-specific `make:doc` command, rather than created by hand:

```bash
php artisan make:doc guide/getting-started
php artisan make:doc guide/overview --order=1
```

The first form creates `docs/guide/getting-started.md`. The `--order` option adds a two-digit numeric prefix to the filename only (not the whole path), so the second form creates `docs/guide/01-overview.md`, matching the numbered convention used in this doc set. Either way, the title is derived from the path and a starter `## Overview` heading is included, and the command refuses to run if the file already exists.

### `app/Console/Commands/MakeDocCommand.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeDocCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:doc
        {name : The doc path, e.g. guide/getting-started}
        {--order= : Optional numeric prefix, e.g. --order=1 for 01-getting-started.md}';

    /**
     * The console command description.
     */
    protected $description = 'Scaffold a new documentation page under docs/';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = trim($this->argument('name'), '/');
        $directory = dirname($path);
        $slug = basename($path);
        $title = Str::headline($slug);

        if ($order = $this->option('order')) {
            $slug = str_pad((string) $order, 2, '0', STR_PAD_LEFT)."-{$slug}";
        }

        $relativePath = $directory === '.' ? $slug : "{$directory}/{$slug}";
        $fullPath = base_path("docs/{$relativePath}.md");

        if (File::exists($fullPath)) {
            $this->error("Doc page already exists: docs/{$relativePath}.md");

            return self::FAILURE;
        }

        File::ensureDirectoryExists(dirname($fullPath));

        File::put($fullPath, <<<MD
        # {$title}

        ## Overview

        _Describe what this page covers._

        MD);

        $this->info("Created docs/{$relativePath}.md");

        return self::SUCCESS;
    }
}
```

`Str::headline()` turns `getting-started` into `Getting Started` before the `--order` prefix is applied, so the page title never picks up the numeric prefix. Nested paths (`guide/getting-started`) create the intermediate directory automatically via `File::ensureDirectoryExists()`.

### Commands used for this doc set

```bash
php artisan make:doc guide/readme
php artisan make:doc guide/overview --order=1
php artisan make:doc guide/service-layer --order=2
php artisan make:doc guide/actions --order=3
php artisan make:doc guide/audit-logging --order=4
php artisan make:doc guide/authorisation --order=5
php artisan make:doc guide/routes --order=6
php artisan make:doc guide/frontend-vue-patterns --order=7
php artisan make:doc guide/frontend-types --order=8
php artisan make:doc guide/cli-reference --order=9
php artisan make:doc guide/plans-module --order=10
php artisan make:doc guide/example-models --order=11
```

## Migrations: standard audit columns

Every new migration for an audited model should include:

```php
$table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
$table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
$table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
$table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
$table->timestamp('restored_at')->nullable();
$table->softDeletes();
$table->timestamps();
```

And the model casts the audit timestamps as `datetime`, via a `casts()` method rather than the `$casts` property:

```php
protected function casts(): array
{
    return [
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];
}
```

See [Example Models](11-example-models.md) for the full `casts()` method on `Company` and `User`, including non-audit casts (`array`, `integer`, `hashed`).
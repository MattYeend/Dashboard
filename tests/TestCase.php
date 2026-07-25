<?php

namespace Tests;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    /**
     * Whether the roles-and-permissions seeding listener has already
     * been registered for this test process.
     */
    protected static bool $rolePermissionListenerRegistered = false;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->withoutVite();

        $this->registerRolePermissionSeedingListener();
    }

    /**
     * Seed roles and permissions the moment migrations finish running.
     *
     * Pest's `uses(LazilyRefreshDatabase::class)` composes its own no-op
     * afterRefreshingDatabase() directly into each test file's class,
     * which always takes precedence over the version inherited from this
     * abstract TestCase — so overriding afterRefreshingDatabase() here
     * never actually fires. Listening for MigrationsEnded instead runs
     * exactly once, right after `migrate` completes and before any
     * per-test transaction wrapping begins, regardless of which
     * refresh-database trait a given test file composes.
     */
    protected function registerRolePermissionSeedingListener(): void
    {
        if (static::$rolePermissionListenerRegistered) {
            return;
        }

        Event::listen(MigrationsEnded::class, function () {
            setPermissionsTeamId(1);

            $this->seed(RolePermissionSeeder::class);
        });

        static::$rolePermissionListenerRegistered = true;
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}

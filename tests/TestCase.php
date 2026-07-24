<?php

namespace Tests;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->withoutVite();
    }

    /**
     * Seed roles and permissions once, immediately after the test database is migrated.
     *
     * Runs a single time per test-suite database refresh (works with both
     * RefreshDatabase and LazilyRefreshDatabase), so every test - including
     * those using CreatesUsers::adminUser()/superAdminUser() - has real
     * permission grants to check against, not just an empty role shell.
     */
    protected function afterRefreshingDatabase()
    {
        setPermissionsTeamId(1);

        $this->seed(RolePermissionSeeder::class);
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}

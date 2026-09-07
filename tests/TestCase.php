<?php

namespace Tests;

use App\Models\Organisation;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    protected $seed = true;

    protected $seeder = RolePermissionSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->withoutVite();

        $this->setUpTestOrganisation();
    }

    protected function setUpTestOrganisation(): Organisation
    {
        $organisation = Organisation::query()->firstOrCreate(
            [
                'slug' => 'test_organisation',
            ],
            [
                'name' => 'Test Organisation',
            ],
        );

        $organisation->makeCurrent();

        setPermissionsTeamId($organisation->id);

        return $organisation;
    }

    protected function skipUnlessFortifyHas(
        string $feature,
        ?string $message = null,
    ): void {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped(
                $message ?? "Fortify feature [{$feature}] is not enabled.",
            );
        }
    }
}

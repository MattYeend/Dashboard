<?php

namespace Tests;

use App\Models\Organisation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    protected $seed = true;

    protected $seeder = RolePermissionSeeder::class;

    protected Organisation $testOrganisation;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->withoutVite();

        $this->testOrganisation = $this->setUpTestOrganisation();
    }

    protected function setUpTestOrganisation(): Organisation
    {
        $organisation = Organisation::query()->firstOrCreate(
            ['slug' => 'test_organisation'],
            ['name' => 'Test Organisation'],
        );

        $organisation->makeCurrent();

        setPermissionsTeamId($organisation->id);

        return $organisation;
    }

    /**
     * Authenticate as the given user for the request, and ensure the
     * tenant resolved by SessionOrganisationFinder during the request
     * matches the organisation active when test data was created,
     * rather than falling back to an unrelated default organisation.
     */
    public function actingAs(Authenticatable $user, $guard = null)
    {
        $this->withSession([
            'current_organisation_id' => $this->testOrganisation->id,
        ]);

        return parent::actingAs($user, $guard);
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

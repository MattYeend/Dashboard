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
     *
     * Also ensures the user holds an active membership row for that
     * organisation, since SessionOrganisationFinder and
     * PolicyAuthorisationService both require an active
     * organisation_user pivot row (not merely a session value) to
     * resolve or authorise against it — super admins are exempt from
     * this check at the application level, but attaching them here
     * too keeps every actingAs() user's organisation_user state
     * consistent and avoids relying on that bypass in tests that
     * aren't specifically exercising it.
     */
    public function actingAs(Authenticatable $user, $guard = null)
    {
        if ($user instanceof User) {
            $this->testOrganisation->addActiveMember($user->id);
        }

        $this->withSession([
            'current_organisation_id' => $this->testOrganisation->id,
        ]);

        return parent::actingAs($user, $guard);
    }

    /**
     * Authenticate as the given user without attaching them to the
     * test organisation or setting a current_organisation_id session
     * value.
     *
     * Use this instead of actingAs() specifically for tests exercising
     * "user has no organisation membership" / no-current-tenant
     * behaviour, where actingAs()'s automatic active-membership setup
     * would defeat the scenario under test.
     */
    public function actingAsWithoutOrganisation(Authenticatable $user, $guard = null)
    {
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

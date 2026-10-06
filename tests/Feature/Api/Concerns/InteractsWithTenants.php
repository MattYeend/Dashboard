<?php

namespace Tests\Feature\Api\Concerns;

use App\Enums\TokenAbility;
use App\Models\Organisation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

/**
 * @mixin \Tests\TestCase
 */
trait InteractsWithTenants
{
    /**
     * Seed roles and permissions once the database has been refreshed.
     *
     * Laravel calls setUp{TraitName} after RefreshDatabase has run.
     */
    protected function setUpInteractsWithTenants(): void
    {
        $this->seed(RolePermissionSeeder::class);

        // The seeder leaves its own organisation current. Start with no tenant.
        Organisation::forgetCurrent();
    }

    /**
     * Make sure no tenant leaks from one test into the next.
     */
    protected function tearDownInteractsWithTenants(): void
    {
        Organisation::forgetCurrent();
    }

    /**
     * Create an organisation.
     */
    protected function makeOrganisation(): Organisation
    {
        return Organisation::factory()->create();
    }

    /**
     * Create a user who is an active member of the organisation with the Admin role.
     */
    protected function makeMember(Organisation $organisation): User
    {
        $user = User::factory()->create();

        $this->joinOrganisation($user, $organisation);

        return $user;
    }

    /**
     * Add an existing user to an organisation as an active Admin.
     *
     * The seeder only creates roles for its own organisation, so the seeded
     * Admin role's permissions are copied into the target organisation.
     * If the seeded roles are global, findOrCreate returns the seeded role
     * itself and the sync is a no-op.
     */
    protected function joinOrganisation(User $user, Organisation $organisation): void
    {
        $organisation->addActiveMember($user->id);

        $permissions = Role::query()
            ->where('name', 'Admin')
            ->where('guard_name', 'web')
            ->oldest('id')
            ->firstOrFail()
            ->permissions;

        $organisation->execute(function () use ($user, $permissions): void {
            $role = Role::findOrCreate('Admin', 'web');
            $role->syncPermissions($permissions);
            $user->assignRole($role);
        });
    }

    /**
     * Authenticate the next requests as a Sanctum bearer token or as a stateful SPA session.
     *
     * Bearer clients carry no organisation hint, so the finder falls back
     * to the user's earliest active membership. SPA clients get the
     * organisation id placed on the session, as the switcher would.
     */
    protected function authenticateAs(
        User $user,
        ?Organisation $organisation,
        string $mode,
    ): static {
        if ($mode === 'bearer') {
            $abilities = array_map(
                fn (TokenAbility $ability): string => $ability->value,
                TokenAbility::cases(),
            );

            return $this->withToken(
                $user->createToken('test', $abilities)->plainTextToken
            );
        }

        $this->actingAsWithoutOrganisation($user)->withHeaders(['Origin' => 'http://localhost']);

        return $organisation === null
            ? $this
            : $this->withSession(['current_organisation_id' => $organisation->id]);
    }
}

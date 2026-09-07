<?php

namespace Tests\Concerns;

use App\Models\Organisation;
use App\Models\User;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Permission\Models\Permission;

trait CreatesUsers
{
    protected function setUpCreatesUsers(): void
    {
        $organisation = Organisation::factory()->create();

        $organisation->makeCurrent();

        setPermissionsTeamId($organisation->id);
    }

    public function adminUser(): User
    {
        /** @var Organisation $organisation */
        $organisation = Tenant::current();

        $user = User::factory()->create();

        $organisation->users()->attach($user);

        setPermissionsTeamId($organisation->id);

        $user->assignRole('Admin');

        return $user;
    }

    public function superAdminUser(): User
    {
        /** @var Organisation $organisation */
        $organisation = Tenant::current();

        $user = User::factory()->create();

        $organisation->users()->attach($user);

        setPermissionsTeamId($organisation->id);

        $user->assignRole('Super Admin');

        return $user;
    }

    public function normalUser(): User
    {
        /** @var Organisation $organisation */
        $organisation = Tenant::current();

        $user = User::factory()->create();

        $organisation->users()->attach($user);

        setPermissionsTeamId($organisation->id);

        $user->assignRole('User');

        return $user;
    }

    public function userWithNoPermissions(): User
    {
        /** @var Organisation $organisation */
        $organisation = Tenant::current();

        $user = User::factory()->create();

        $organisation->users()->attach($user);

        setPermissionsTeamId($organisation->id);

        return $user;
    }

    /**
     * Create a user with an arbitrary set of permissions attached directly
     * (no role), for testing permission-specific authorisation logic in
     * isolation from any particular role's full permission set.
     */
    public function userWithPermissions(array $permissions): User
    {
        /** @var Organisation $organisation */
        $organisation = Tenant::current();

        $user = User::factory()->create();

        $organisation->users()->attach($user);

        setPermissionsTeamId($organisation->id);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $user->givePermissionTo($permissions);

        return $user;
    }
}

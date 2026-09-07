<?php

namespace Tests\Concerns;

use App\Models\Organisation;
use App\Models\Permission;
use App\Models\User;

trait CreatesUsers
{
    protected function testOrganisation(): Organisation
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

    /**
     * Create a user who belongs to the test organisation.
     */
    protected function createTenantUser(): User
    {
        $organisation = $this->testOrganisation();

        $user = User::factory()->create();

        $organisation->users()->attach($user);

        setPermissionsTeamId($organisation->id);

        return $user;
    }

    public function adminUser(): User
    {
        $user = $this->createTenantUser();

        $user->assignRole('Admin');

        return $user;
    }

    public function superAdminUser(): User
    {
        $user = $this->createTenantUser();

        $user->assignRole('Super Admin');

        return $user;
    }

    public function normalUser(): User
    {
        $user = $this->createTenantUser();

        $user->assignRole('User');

        return $user;
    }

    public function userWithNoPermissions(): User
    {
        return $this->createTenantUser();
    }

    public function userWithPermissions(array $permissions): User
    {
        $organisation = $this->testOrganisation();

        $user = User::factory()->create();

        $organisation->users()->attach($user);

        setPermissionsTeamId($organisation->id);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $user->givePermissionTo($permissions);

        return $user;
    }
}

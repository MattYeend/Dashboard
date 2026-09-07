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

    public function adminUser(): User
    {
        $organisation = $this->testOrganisation();

        $user = User::factory()->create();

        $organisation->users()->attach($user);

        setPermissionsTeamId($organisation->id);

        $user->assignRole('Admin');

        return $user;
    }

    public function superAdminUser(): User
    {
        $organisation = $this->testOrganisation();

        $user = User::factory()->create();

        $organisation->users()->attach($user);

        setPermissionsTeamId($organisation->id);

        $user->assignRole('Super Admin');

        return $user;
    }

    public function normalUser(): User
    {
        $organisation = $this->testOrganisation();

        $user = User::factory()->create();

        $organisation->users()->attach($user);

        setPermissionsTeamId($organisation->id);

        $user->assignRole('User');

        return $user;
    }

    public function userWithNoPermissions(): User
    {
        $organisation = $this->testOrganisation();

        $user = User::factory()->create();

        $organisation->users()->attach($user);

        setPermissionsTeamId($organisation->id);

        return $user;
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

        setPermissionsTeamId($organisation->id);

        $user->givePermissionTo($permissions);

        return $user;
    }
}

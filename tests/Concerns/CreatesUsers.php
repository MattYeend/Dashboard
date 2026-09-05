<?php

namespace Tests\Concerns;

use App\Models\User;
use Spatie\Permission\Models\Permission;

trait CreatesUsers
{
    public function adminUser(): User
    {
        $user = User::factory()->create();
        setPermissionsTeamId(1);
        $user->assignRole('Admin');

        return $user;
    }

    public function superAdminUser(): User
    {
        $user = User::factory()->create();
        setPermissionsTeamId(1);
        $user->assignRole('Super Admin');

        return $user;
    }

    public function normalUser(): User
    {
        $user = User::factory()->create();
        setPermissionsTeamId(1);
        $user->assignRole('User');

        return $user;
    }

    public function userWithNoPermissions(): User
    {
        $user = User::factory()->create();
        setPermissionsTeamId(1);

        return $user;
    }

    /**
     * Create a user with an arbitrary set of permissions attached directly
     * (no role), for testing permission-specific authorisation logic in
     * isolation from any particular role's full permission set.
     */
    public function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        setPermissionsTeamId(1);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $user->givePermissionTo($permissions);

        return $user;
    }
}

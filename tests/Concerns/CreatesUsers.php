<?php

namespace Tests\Concerns;

use App\Models\User;

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
}
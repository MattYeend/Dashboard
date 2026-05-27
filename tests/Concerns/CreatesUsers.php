<?php

namespace Tests\Concerns;

use App\Models\User;

trait CreatesUsers
{
    public function adminUser(): User
    {
        $user = User::factory()->create([
            'is_admin' => true,
            'is_super_admin' => false,
            'is_user' => true,
        ]);

        $user->assignRole('admin');

        return $user;
    }

    public function superAdminUser(): User
    {
        $user = User::factory()->create([
            'is_admin' => true,
            'is_super_admin' => true,
            'is_user' => true,
        ]);

        $user->assignRole('Super Admin');

        return $user;
    }

    public function normalUser(): User
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'is_super_admin' => false,
            'is_user' => true,
        ]);

        $user->assignRole('User');

        return $user;
    }
}
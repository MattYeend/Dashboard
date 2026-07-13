<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            ContactSeeder::class,
            TaskStatusSeeder::class,
            TaskSeeder::class,
            OrderStatusSeeder::class,
            OrderSeeder::class,
            IndustrySeeder::class,
            CompanySeeder::class,
            PlanSeeder::class,
            AddressSeeder::class,
        ]);
    }
}

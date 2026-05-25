<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin User
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Create Admin Users
        $admin1 = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        $admin2 = User::firstOrCreate(
            ['email' => 'john.admin@example.com'],
            [
                'name' => 'John Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Create Regular Users
        $user1 = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'jane.smith@example.com'],
            [
                'name' => 'Jane Smith',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        $user3 = User::firstOrCreate(
            ['email' => 'bob.johnson@example.com'],
            [
                'name' => 'Bob Johnson',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Sales Manager
        $salesManager = User::firstOrCreate(
            ['email' => 'sarah.sales@example.com'],
            [
                'name' => 'Sarah Sales Manager',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // HR Manager
        $hrManager = User::firstOrCreate(
            ['email' => 'henry.hr@example.com'],
            [
                'name' => 'Henry HR Manager',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // System Administrator
        $sysAdmin = User::firstOrCreate(
            ['email' => 'steve.sysadmin@example.com'],
            [
                'name' => 'Steve SysAdmin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Accountant
        $accountant = User::firstOrCreate(
            ['email' => 'alice.accountant@example.com'],
            [
                'name' => 'Alice Accountant',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Marketing Specialist
        $marketing = User::firstOrCreate(
            ['email' => 'mike.marketing@example.com'],
            [
                'name' => 'Mike Marketing',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // IT Support
        $itSupport = User::firstOrCreate(
            ['email' => 'emma.support@example.com'],
            [
                'name' => 'Emma IT Support',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Sales Representative
        $salesRep = User::firstOrCreate(
            ['email' => 'david.sales@example.com'],
            [
                'name' => 'David Sales Rep',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Customer Service
        $customerService = User::firstOrCreate(
            ['email' => 'lisa.service@example.com'],
            [
                'name' => 'Lisa Customer Service',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Project Manager
        $projectManager = User::firstOrCreate(
            ['email' => 'tom.manager@example.com'],
            [
                'name' => 'Tom Project Manager',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Developer
        $developer = User::firstOrCreate(
            ['email' => 'anna.dev@example.com'],
            [
                'name' => 'Anna Developer',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Designer
        $designer = User::firstOrCreate(
            ['email' => 'chris.design@example.com'],
            [
                'name' => 'Chris Designer',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Content Writer
        $writer = User::firstOrCreate(
            ['email' => 'rachel.writer@example.com'],
            [
                'name' => 'Rachel Writer',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Data Analyst
        $analyst = User::firstOrCreate(
            ['email' => 'kevin.analyst@example.com'],
            [
                'name' => 'Kevin Data Analyst',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Operations Manager
        $operations = User::firstOrCreate(
            ['email' => 'maria.operations@example.com'],
            [
                'name' => 'Maria Operations',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Unverified User
        $unverified = User::firstOrCreate(
            ['email' => 'unverified@example.com'],
            [
                'name' => 'Unverified User',
                'email_verified_at' => null,
                'password' => Hash::make('password'),
            ]
        );

        // Test User
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        $this->command->info('Users created/verified successfully!');
        $this->command->info('');
        $this->command->info('Login Credentials (all use password: password):');
        $this->command->info('Super Admin: superadmin@example.com');
        $this->command->info('Admin: admin@example.com');
        $this->command->info('Regular User: user@example.com');
        $this->command->info('Test User: test@example.com');
        $this->command->info('');
        $this->command->info('Additional users created for different roles:');
        $this->command->info('- Sales: sarah.sales@example.com, david.sales@example.com');
        $this->command->info('- HR: henry.hr@example.com');
        $this->command->info('- IT: steve.sysadmin@example.com, emma.support@example.com');
        $this->command->info('- Finance: alice.accountant@example.com');
        $this->command->info('- Marketing: mike.marketing@example.com');
        $this->command->info('- Development: anna.dev@example.com');
         $this->command->info('- And more...');
    }
}
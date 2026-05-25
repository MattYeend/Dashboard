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
        $superAdmin->assignRole('Super Admin');

        // Create Admin Users
        $admin1 = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $admin1->assignRole('Admin');

        $admin2 = User::firstOrCreate(
            ['email' => 'john.admin@example.com'],
            [
                'name' => 'John Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $admin2->assignRole('Admin');

        // Create Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Sarah Manager',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $manager->assignRole('Manager');

        // Create Editor
        $editor = User::firstOrCreate(
            ['email' => 'editor@example.com'],
            [
                'name' => 'Emily Editor',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $editor->assignRole('Editor');

        // Create Moderator
        $moderator = User::firstOrCreate(
            ['email' => 'moderator@example.com'],
            [
                'name' => 'Mike Moderator',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $moderator->assignRole('Moderator');

        // Create Support User
        $support = User::firstOrCreate(
            ['email' => 'support@example.com'],
            [
                'name' => 'Lisa Support',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $support->assignRole('Support');

        // Create Analyst
        $analyst = User::firstOrCreate(
            ['email' => 'analyst@example.com'],
            [
                'name' => 'David Analyst',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $analyst->assignRole('Analyst');

        // Create Viewer
        $viewer = User::firstOrCreate(
            ['email' => 'viewer@example.com'],
            [
                'name' => 'Rachel Viewer',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $viewer->assignRole('Viewer');

        // Create Regular Users
        $user1 = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $user1->assignRole('User');

        $user2 = User::firstOrCreate(
            ['email' => 'jane.smith@example.com'],
            [
                'name' => 'Jane Smith',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $user2->assignRole('User');

        $user3 = User::firstOrCreate(
            ['email' => 'bob.johnson@example.com'],
            [
                'name' => 'Bob Johnson',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $user3->assignRole('User');

        // Additional staff members
        $contentWriter = User::firstOrCreate(
            ['email' => 'writer@example.com'],
            [
                'name' => 'Anna Writer',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $contentWriter->assignRole('Editor');

        $customerSupport = User::firstOrCreate(
            ['email' => 'cs@example.com'],
            [
                'name' => 'Tom Support',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $customerSupport->assignRole('Support');

        $dataAnalyst = User::firstOrCreate(
            ['email' => 'data@example.com'],
            [
                'name' => 'Kevin Data',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $dataAnalyst->assignRole('Analyst');

        $contentMod = User::firstOrCreate(
            ['email' => 'mod@example.com'],
            [
                'name' => 'Chris Moderator',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $contentMod->assignRole('Moderator');

        // Create Guest User
        $guest = User::firstOrCreate(
            ['email' => 'guest@example.com'],
            [
                'name' => 'Guest User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $guest->assignRole('Guest');

        // Unverified User
        $unverified = User::firstOrCreate(
            ['email' => 'unverified@example.com'],
            [
                'name' => 'Unverified User',
                'email_verified_at' => null,
                'password' => Hash::make('password'),
            ]
        );
        $unverified->assignRole('User');

        // Test User
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $testUser->assignRole('User');

        $this->command->info('Users created/verified and roles assigned successfully!');
        $this->command->info('');
        $this->command->info('Login Credentials (all use password: password):');
        $this->command->info('');
        $this->command->info('Super Admin: superadmin@example.com (Full access)');
        $this->command->info('Admin: admin@example.com, john.admin@example.com (Administrative access)');
        $this->command->info('Manager: manager@example.com (User & content management)');
        $this->command->info('Editor: editor@example.com, writer@example.com (Content management)');
        $this->command->info('Moderator: moderator@example.com, mod@example.com (Content & user moderation)');
        $this->command->info('Support: support@example.com, cs@example.com (Customer support)');
        $this->command->info('Analyst: analyst@example.com, data@example.com (Reports & analytics)');
        $this->command->info('Viewer: viewer@example.com (Read-only access)');
        $this->command->info('User: user@example.com, test@example.com, and others (Basic access)');
        $this->command->info('Guest: guest@example.com (Minimal access)');
    }
}

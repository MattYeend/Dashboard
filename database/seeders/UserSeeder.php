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
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'role' => 'super_admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'meta' => json_encode([
                    'department' => 'Management',
                    'position' => 'Super Administrator',
                    'phone' => '+1-555-0100',
                ]),
            ]
        );
        $superAdmin->assignRole('Super Admin');

        // Create Admin Users
        $admin1 = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'role' => 'admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $superAdmin->id,
                'meta' => json_encode([
                    'department' => 'Management',
                    'position' => 'Administrator',
                    'phone' => '+1-555-0101',
                ]),
            ]
        );
        $admin1->assignRole('Admin');

        $admin2 = User::updateOrCreate(
            ['email' => 'john.admin@example.com'],
            [
                'name' => 'John Admin',
                'role' => 'admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $superAdmin->id,
                'meta' => json_encode([
                    'department' => 'Management',
                    'position' => 'Administrator',
                    'phone' => '+1-555-0102',
                ]),
            ]
        );
        $admin2->assignRole('Admin');

        // Create Manager
        $manager = User::updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Sarah Manager',
                'role' => 'user',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'Operations',
                    'position' => 'Manager',
                    'phone' => '+1-555-0103',
                ]),
            ]
        );
        $manager->assignRole('Manager');

        // Create Editor
        $editor = User::updateOrCreate(
            ['email' => 'editor@example.com'],
            [
                'name' => 'Emily Editor',
                'role' => 'user',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'Content',
                    'position' => 'Editor',
                    'phone' => '+1-555-0104',
                ]),
            ]
        );
        $editor->assignRole('Editor');

        // Create Moderator
        $moderator = User::updateOrCreate(
            ['email' => 'moderator@example.com'],
            [
                'name' => 'Mike Moderator',
                'role' => 'user',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'Content',
                    'position' => 'Moderator',
                    'phone' => '+1-555-0105',
                ]),
            ]
        );
        $moderator->assignRole('Moderator');

        // Create Support User
        $support = User::updateOrCreate(
            ['email' => 'support@example.com'],
            [
                'name' => 'Lisa Support',
                'role' => 'user',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'Support',
                    'position' => 'Support Agent',
                    'phone' => '+1-555-0106',
                ]),
            ]
        );
        $support->assignRole('Support');

        // Create Analyst
        $analyst = User::updateOrCreate(
            ['email' => 'analyst@example.com'],
            [
                'name' => 'David Analyst',
                'role' => 'user',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'Analytics',
                    'position' => 'Data Analyst',
                    'phone' => '+1-555-0107',
                ]),
            ]
        );
        $analyst->assignRole('Analyst');

        // Create Viewer
        $viewer = User::updateOrCreate(
            ['email' => 'viewer@example.com'],
            [
                'name' => 'Rachel Viewer',
                'role' => 'user',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'Operations',
                    'position' => 'Viewer',
                    'phone' => '+1-555-0108',
                ]),
            ]
        );
        $viewer->assignRole('Viewer');

        // Create Regular Users
        $user1 = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'role' => 'user',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'General',
                    'position' => 'User',
                    'phone' => '+1-555-0109',
                ]),
            ]
        );
        $user1->assignRole('User');

        $user2 = User::updateOrCreate(
            ['email' => 'jane.smith@example.com'],
            [
                'name' => 'Jane Smith',
                'role' => 'user',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'General',
                    'position' => 'User',
                    'phone' => '+1-555-0110',
                ]),
            ]
        );
        $user2->assignRole('User');

        $user3 = User::updateOrCreate(
            ['email' => 'bob.johnson@example.com'],
            [
                'name' => 'Bob Johnson',
                'role' => 'user',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'General',
                    'position' => 'User',
                    'phone' => '+1-555-0111',
                ]),
            ]
        );
        $user3->assignRole('User');

        // Additional staff members
        $contentWriter = User::updateOrCreate(
            ['email' => 'writer@example.com'],
            [
                'name' => 'Anna Writer',
                'role' => 'user',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'Content',
                    'position' => 'Content Writer',
                    'phone' => '+1-555-0112',
                ]),
            ]
        );
        $contentWriter->assignRole('Editor');

        $customerSupport = User::updateOrCreate(
            ['email' => 'cs@example.com'],
            [
                'name' => 'Tom Support',
                'role' => 'user',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'Support',
                    'position' => 'Customer Support',
                    'phone' => '+1-555-0113',
                ]),
            ]
        );
        $customerSupport->assignRole('Support');

        $dataAnalyst = User::updateOrCreate(
            ['email' => 'data@example.com'],
            [
                'name' => 'Kevin Data',
                'role' => 'user',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'Analytics',
                    'position' => 'Data Analyst',
                    'phone' => '+1-555-0114',
                ]),
            ]
        );
        $dataAnalyst->assignRole('Analyst');

        $contentMod = User::updateOrCreate(
            ['email' => 'mod@example.com'],
            [
                'name' => 'Chris Moderator',
                'role' => 'user',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'Content',
                    'position' => 'Content Moderator',
                    'phone' => '+1-555-0115',
                ]),
            ]
        );
        $contentMod->assignRole('Moderator');

        // Create Guest User
        $guest = User::updateOrCreate(
            ['email' => 'guest@example.com'],
            [
                'name' => 'Guest User',
                'role' => 'user',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'General',
                    'position' => 'Guest',
                    'phone' => null,
                ]),
            ]
        );
        $guest->assignRole('Guest');

        // Unverified User
        $unverified = User::updateOrCreate(
            ['email' => 'unverified@example.com'],
            [
                'name' => 'Unverified User',
                'role' => 'user',
                'email_verified_at' => null,
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'General',
                    'position' => 'User',
                    'phone' => null,
                ]),
            ]
        );
        $unverified->assignRole('User');

        // Test User
        $testUser = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'role' => 'user',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $admin1->id,
                'meta' => json_encode([
                    'department' => 'General',
                    'position' => 'Tester',
                    'phone' => null,
                ]),
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

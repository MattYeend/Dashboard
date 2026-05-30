<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard
            'view dashboard',
            'view statistics',
            'view charts',
            'export dashboard data',

            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            'restore users',
            'force delete users',
            'import users',
            'export users',

            // Role management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'assign roles',

            // Permission management
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            'assign permissions',

            // Profile management
            'view own profile',
            'edit own profile',
            'delete own profile',
            'change own password',
            'view other profiles',

            // Content management
            'view content',
            'create content',
            'edit content',
            'delete content',
            'restore content',
            'force delete content',
            'publish content',
            'unpublish content',

            // Settings
            'view settings',
            'edit settings',
            'view system settings',
            'edit system settings',
            'view security settings',
            'edit security settings',

            // Notifications
            'view notifications',
            'create notifications',
            'edit notifications',
            'delete notifications',
            'send notifications',

            // Activity logs
            'view activity logs',
            'export activity logs',
            'delete activity logs',

            // Reports
            'view reports',
            'create reports',
            'edit reports',
            'delete reports',
            'export reports',
            'schedule reports',

            // File management
            'view files',
            'upload files',
            'download files',
            'delete files',
            'restore files',
            'force delete files',

            // Audit trail
            'view audit trail',
            'export audit trail',

            // Backup
            'view backups',
            'create backups',
            'restore backups',
            'delete backups',
            'import backups',
            'export backups',

            // API access
            'view api keys',
            'create api keys',
            'revoke api keys',
            'view api logs',

            // System maintenance
            'clear cache',
            'view system info',
            'run maintenance',
            'view logs',

            // Contact information
            'view contact information',
            'create contact information',
            'edit contact information',
            'delete contact information',
            'restore contact information',
            'force delete contact information',
            'import contact information',
            'export contact information',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin role - has all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin role - has most permissions except system-critical ones
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $adminPermissions = [
            'view dashboard',
            'view statistics',
            'view charts',
            'export dashboard data',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'restore users',
            'import users',
            'export users',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'assign roles',
            'view permissions',
            'assign permissions',
            'view own profile',
            'edit own profile',
            'change own password',
            'view other profiles',
            'view content',
            'create content',
            'edit content',
            'delete content',
            'restore content',
            'publish content',
            'unpublish content',
            'view settings',
            'edit settings',
            'view notifications',
            'create notifications',
            'edit notifications',
            'delete notifications',
            'send notifications',
            'view activity logs',
            'export activity logs',
            'view reports',
            'create reports',
            'edit reports',
            'delete reports',
            'export reports',
            'schedule reports',
            'view files',
            'upload files',
            'download files',
            'delete files',
            'restore files',
            'view audit trail',
            'export audit trail',
            'view backups',
            'create backups',
            'restore backups',
            'delete backups',
            'import backups',
            'export backups',
            'view api keys',
            'create api keys',
            'revoke api keys',
            'view api logs',
            'clear cache',
            'view system info',
            'view logs',
            'view contact information',
            'create contact information',
            'edit contact information',
            'delete contact information',
            'restore contact information',
            'import contact information',
            'export contact information',
        ];
        $admin->givePermissionTo($adminPermissions);

        // Manager role - can manage content and users but limited system access
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $managerPermissions = [
            'view dashboard',
            'view statistics',
            'view charts',
            'export dashboard data',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'import users',
            'export users',
            'view roles',
            'assign roles',
            'view own profile',
            'edit own profile',
            'change own password',
            'view other profiles',
            'view content',
            'create content',
            'edit content',
            'delete content',
            'publish content',
            'unpublish content',
            'view settings',
            'view notifications',
            'create notifications',
            'send notifications',
            'view activity logs',
            'export activity logs',
            'view reports',
            'create reports',
            'edit reports',
            'export reports',
            'view files',
            'upload files',
            'download files',
            'delete files',
            'view audit trail',
            'view contact information',
            'create contact information',
            'edit contact information',
            'delete contact information',
            'restore contact information',
            'import contact information',
            'export contact information',
        ];
        $manager->givePermissionTo($managerPermissions);

        // Editor role - focused on content management
        $editor = Role::firstOrCreate(['name' => 'Editor']);
        $editorPermissions = [
            'view dashboard',
            'view statistics',
            'view own profile',
            'edit own profile',
            'change own password',
            'view content',
            'create content',
            'edit content',
            'delete content',
            'publish content',
            'unpublish content',
            'view notifications',
            'view files',
            'upload files',
            'download files',
            'delete files',
            'view reports',
        ];
        $editor->givePermissionTo($editorPermissions);

        // Viewer role - read-only access to most features
        $viewer = Role::firstOrCreate(['name' => 'Viewer']);
        $viewerPermissions = [
            'view dashboard',
            'view statistics',
            'view charts',
            'view users',
            'view own profile',
            'edit own profile',
            'change own password',
            'view content',
            'view notifications',
            'view reports',
            'view files',
            'download files',
            'view contact information',
        ];
        $viewer->givePermissionTo($viewerPermissions);

        // Moderator role - can manage content and moderate users
        $moderator = Role::firstOrCreate(['name' => 'Moderator']);
        $moderatorPermissions = [
            'view dashboard',
            'view statistics',
            'view charts',
            'view users',
            'edit users',
            'view own profile',
            'edit own profile',
            'change own password',
            'view other profiles',
            'view content',
            'create content',
            'edit content',
            'delete content',
            'restore content',
            'publish content',
            'unpublish content',
            'view notifications',
            'create notifications',
            'send notifications',
            'view activity logs',
            'view files',
            'upload files',
            'download files',
            'delete files',
            'view reports',
            'view contact information',
        ];
        $moderator->givePermissionTo($moderatorPermissions);

        // Support role - customer support focused
        $support = Role::firstOrCreate(['name' => 'Support']);
        $supportPermissions = [
            'view dashboard',
            'view statistics',
            'view users',
            'view own profile',
            'edit own profile',
            'change own password',
            'view other profiles',
            'view content',
            'view notifications',
            'create notifications',
            'send notifications',
            'view activity logs',
            'view reports',
            'view files',
            'download files',
            'view contact information',
            'create contact information',
            'edit contact information',
            'delete contact information',
            'restore contact information',
            'import contact information',
            'export contact information',
        ];
        $support->givePermissionTo($supportPermissions);

        // Analyst role - focused on data and reports
        $analyst = Role::firstOrCreate(['name' => 'Analyst']);
        $analystPermissions = [
            'view dashboard',
            'view statistics',
            'view charts',
            'export dashboard data',
            'view users',
            'export users',
            'view own profile',
            'edit own profile',
            'change own password',
            'view content',
            'view activity logs',
            'export activity logs',
            'view reports',
            'create reports',
            'edit reports',
            'export reports',
            'schedule reports',
            'view files',
            'download files',
            'view audit trail',
            'export audit trail',
            'view contact information',
            'export contact information',
        ];
        $analyst->givePermissionTo($analystPermissions);

        // User role - basic permissions for regular users
        $user = Role::firstOrCreate(['name' => 'User']);
        $userPermissions = [
            'view dashboard',
            'view own profile',
            'edit own profile',
            'change own password',
            'view content',
            'view notifications',
            'view files',
            'upload files',
            'download files',
            'view contact information',
        ];
        $user->givePermissionTo($userPermissions);

        // Guest role - minimal read-only access
        $guest = Role::firstOrCreate(['name' => 'Guest']);
        $guestPermissions = [
            'view dashboard',
            'view content',
        ];
        $guest->givePermissionTo($guestPermissions);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('');
        $this->command->info('Roles created:');
        $this->command->info('1. Super Admin - Full system access (all permissions)');
        $this->command->info('2. Admin - Administrative access (most permissions except critical system ops)');
        $this->command->info('3. Manager - User and content management');
        $this->command->info('4. Editor - Content creation and management');
        $this->command->info('5. Viewer - Read-only access to most features');
        $this->command->info('6. Moderator - Content and user moderation');
        $this->command->info('7. Support - Customer support operations');
        $this->command->info('8. Analyst - Data analysis and reporting');
        $this->command->info('9. User - Basic user permissions');
        $this->command->info('10. Guest - Minimal read-only access');
        $this->command->info('');
        $this->command->info('Total permissions created: '.Permission::count());
        $this->command->info('');
        $this->command->info('Permission categories:');
        $this->command->info('- Dashboard & Statistics');
        $this->command->info('- User Management');
        $this->command->info('- Role & Permission Management');
        $this->command->info('- Profile Management');
        $this->command->info('- Content Management');
        $this->command->info('- Settings & Configuration');
        $this->command->info('- Notifications');
        $this->command->info('- Activity Logs & Audit Trail');
        $this->command->info('- Reports & Analytics');
        $this->command->info('- File Management');
        $this->command->info('- Backup & Restore');
        $this->command->info('- API Management');
        $this->command->info('- System Maintenance');
        $this->command->info('- Contact Information');
    }
}

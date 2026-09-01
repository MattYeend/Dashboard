<?php

use App\Models\Log;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

beforeEach(function () {
    setPermissionsTeamId(1);

    Role::firstOrCreate(['name' => 'Admin']);
    Role::firstOrCreate(['name' => 'Super Admin']);
    Role::firstOrCreate(['name' => 'User']);
});

describe('index', function () {
    test('authenticated user with view permission can view the settings page', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/settings')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('settings/Index')
                ->has('setting')
                ->has('permissions')
            );
    });

    test('unauthenticated user cannot view the settings page', function () {
        $this->get('/settings')
            ->assertRedirect('/login');
    });

    test('index loads for a user holding only one of the three view permissions', function () {
        $user = User::factory()->create();
        $user->givePermissionTo('view settings');

        $this->actingAs($user)
            ->get('/settings')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('permissions.can_view_general', true)
                ->where('permissions.can_view_system', false)
                ->where('permissions.can_view_security', false)
            );
    });

    test('permissions prop reflects the user\'s specific edit grants only', function () {
        $user = User::factory()->create();
        $user->givePermissionTo('view settings', 'edit settings', 'view system settings');

        $this->actingAs($user)
            ->get('/settings')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('permissions.can_edit_general', true)
                ->where('permissions.can_view_system', true)
                ->where('permissions.can_edit_system', false)
                ->where('permissions.can_view_security', false)
                ->where('permissions.can_edit_security', false)
            );
    });
});

describe('update general', function () {
    test('authenticated user with permission can update general settings', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'site_name' => 'Updated Dashboard',
            'support_email' => 'help@mattyeend.co.uk',
            'timezone' => 'Europe/London',
            'date_format' => 'Y-m-d',
        ];

        $this->actingAs($superAdmin)
            ->putJson('/settings/general', $payload)
            ->assertStatus(200)
            ->assertJsonFragment(['site_name' => 'Updated Dashboard']);

        $this->assertDatabaseHas('settings', [
            'id' => 1,
            'site_name' => 'Updated Dashboard',
            'support_email' => 'help@mattyeend.co.uk',
        ]);
    });

    test('user without permission cannot update general settings', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->putJson('/settings/general', [
                'site_name' => 'Should Not Save',
                'support_email' => 'nope@mattyeend.co.uk',
                'timezone' => 'Europe/London',
                'date_format' => 'd/m/Y',
            ])
            ->assertStatus(403);
    });

    test('update general fails validation when site_name is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->putJson('/settings/general', [
                'support_email' => 'help@mattyeend.co.uk',
                'timezone' => 'Europe/London',
                'date_format' => 'd/m/Y',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['site_name']);
    });

    test('update general fails validation when support_email is not a valid email', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->putJson('/settings/general', [
                'site_name' => 'Dashboard',
                'support_email' => 'not-an-email',
                'timezone' => 'Europe/London',
                'date_format' => 'd/m/Y',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['support_email']);
    });

    test('update general fails validation when timezone is not a recognised identifier', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->putJson('/settings/general', [
                'site_name' => 'Dashboard',
                'support_email' => 'help@mattyeend.co.uk',
                'timezone' => 'Not/A/Timezone',
                'date_format' => 'd/m/Y',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['timezone']);
    });

    test('logs general settings update with actor id', function () {
        $actor = $this->adminUser();
        $actor->givePermissionTo('view settings', 'edit settings');

        $this->actingAs($actor)
            ->putJson('/settings/general', [
                'site_name' => 'Logged Update',
                'support_email' => 'help@mattyeend.co.uk',
                'timezone' => 'Europe/London',
                'date_format' => 'd/m/Y',
            ])
            ->assertStatus(200);

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_GENERAL_SETTINGS)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKey('before')
            ->and($log->data)->toHaveKey('after');
    });
});

describe('update system', function () {
    test('authenticated user with permission can update system settings', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'maintenance_mode' => true,
            'allow_registrations' => false,
            'default_pagination' => 25,
            'default_locale' => 'en_GB',
        ];

        $this->actingAs($superAdmin)
            ->putJson('/settings/system', $payload)
            ->assertStatus(200)
            ->assertJsonFragment(['maintenance_mode' => true]);

        $this->assertDatabaseHas('settings', [
            'id' => 1,
            'maintenance_mode' => true,
            'default_pagination' => 25,
        ]);
    });

    test('user without permission cannot update system settings', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->putJson('/settings/system', [
                'maintenance_mode' => true,
                'allow_registrations' => true,
                'default_pagination' => 15,
                'default_locale' => 'en_GB',
            ])
            ->assertStatus(403);
    });

    test('update system fails validation when default_pagination is below the minimum', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->putJson('/settings/system', [
                'maintenance_mode' => false,
                'allow_registrations' => true,
                'default_pagination' => 1,
                'default_locale' => 'en_GB',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['default_pagination']);
    });

    test('update system fails validation when default_pagination exceeds the maximum', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->putJson('/settings/system', [
                'maintenance_mode' => false,
                'allow_registrations' => true,
                'default_pagination' => 500,
                'default_locale' => 'en_GB',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['default_pagination']);
    });

    test('logs system settings update with actor id', function () {
        $actor = $this->adminUser();
        $actor->givePermissionTo('view system settings', 'edit system settings');

        $this->actingAs($actor)
            ->putJson('/settings/system', [
                'maintenance_mode' => false,
                'allow_registrations' => true,
                'default_pagination' => 20,
                'default_locale' => 'en_GB',
            ])
            ->assertStatus(200);

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_SYSTEM_SETTINGS)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKey('after');
    });
});

describe('update security', function () {
    test('authenticated user with permission can update security settings', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'two_factor_required' => true,
            'session_timeout_minutes' => 60,
            'max_login_attempts' => 5,
            'password_expiry_days' => 90,
        ];

        $this->actingAs($superAdmin)
            ->putJson('/settings/security', $payload)
            ->assertStatus(200)
            ->assertJsonFragment(['two_factor_required' => true]);

        $this->assertDatabaseHas('settings', [
            'id' => 1,
            'two_factor_required' => true,
            'session_timeout_minutes' => 60,
        ]);
    });

    test('security settings accept a null password_expiry_days', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->putJson('/settings/security', [
                'two_factor_required' => false,
                'session_timeout_minutes' => 120,
                'max_login_attempts' => 5,
                'password_expiry_days' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('settings', [
            'id' => 1,
            'password_expiry_days' => null,
        ]);
    });

    test('user without permission cannot update security settings', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->putJson('/settings/security', [
                'two_factor_required' => true,
                'session_timeout_minutes' => 60,
                'max_login_attempts' => 5,
                'password_expiry_days' => null,
            ])
            ->assertStatus(403);
    });

    test('update security fails validation when session_timeout_minutes is below the minimum', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->putJson('/settings/security', [
                'two_factor_required' => false,
                'session_timeout_minutes' => 1,
                'max_login_attempts' => 5,
                'password_expiry_days' => null,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['session_timeout_minutes']);
    });

    test('update security fails validation when max_login_attempts is below the minimum', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->putJson('/settings/security', [
                'two_factor_required' => false,
                'session_timeout_minutes' => 60,
                'max_login_attempts' => 1,
                'password_expiry_days' => null,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['max_login_attempts']);
    });

    test('logs security settings update with actor id', function () {
        $actor = $this->adminUser();
        $actor->givePermissionTo('view security settings', 'edit security settings');

        $this->actingAs($actor)
            ->putJson('/settings/security', [
                'two_factor_required' => true,
                'session_timeout_minutes' => 30,
                'max_login_attempts' => 5,
                'password_expiry_days' => null,
            ])
            ->assertStatus(200);

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_SECURITY_SETTINGS)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKey('after');
    });
});

describe('permission boundary', function () {
    test('user with edit settings but not edit system settings cannot update system settings', function () {
        $user = User::factory()->create();
        $user->givePermissionTo('view settings', 'edit settings');

        $this->actingAs($user)
            ->putJson('/settings/system', [
                'maintenance_mode' => true,
                'allow_registrations' => false,
                'default_pagination' => 15,
                'default_locale' => 'en_GB',
            ])
            ->assertStatus(403);

        $this->assertDatabaseMissing('settings', ['maintenance_mode' => true]);
    });

    test('user with edit settings but not edit security settings cannot update security settings', function () {
        $user = User::factory()->create();
        $user->givePermissionTo('view settings', 'edit settings');

        $this->actingAs($user)
            ->putJson('/settings/security', [
                'two_factor_required' => true,
                'session_timeout_minutes' => 60,
                'max_login_attempts' => 5,
                'password_expiry_days' => null,
            ])
            ->assertStatus(403);

        $this->assertDatabaseMissing('settings', ['two_factor_required' => true]);
    });

    test('user with edit system settings but not edit settings cannot update general settings', function () {
        $user = User::factory()->create();
        $user->givePermissionTo('view system settings', 'edit system settings');

        $this->actingAs($user)
            ->putJson('/settings/general', [
                'site_name' => 'Should Not Save',
                'support_email' => 'help@mattyeend.co.uk',
                'timezone' => 'Europe/London',
                'date_format' => 'd/m/Y',
            ])
            ->assertStatus(403);
    });

    test('user with edit security settings only cannot update general or system settings', function () {
        $user = User::factory()->create();
        $user->givePermissionTo('view security settings', 'edit security settings');

        $this->actingAs($user)
            ->putJson('/settings/general', [
                'site_name' => 'Should Not Save',
                'support_email' => 'help@mattyeend.co.uk',
                'timezone' => 'Europe/London',
                'date_format' => 'd/m/Y',
            ])
            ->assertStatus(403);

        $this->actingAs($user)
            ->putJson('/settings/system', [
                'maintenance_mode' => true,
                'allow_registrations' => false,
                'default_pagination' => 15,
                'default_locale' => 'en_GB',
            ])
            ->assertStatus(403);
    });

    test('a user holding all three edit permissions can update all three groups', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(
            'view settings', 'edit settings',
            'view system settings', 'edit system settings',
            'view security settings', 'edit security settings',
        );

        $this->actingAs($user)
            ->putJson('/settings/general', [
                'site_name' => 'All Access',
                'support_email' => 'help@mattyeend.co.uk',
                'timezone' => 'Europe/London',
                'date_format' => 'd/m/Y',
            ])
            ->assertStatus(200);

        $this->actingAs($user)
            ->putJson('/settings/system', [
                'maintenance_mode' => false,
                'allow_registrations' => true,
                'default_pagination' => 15,
                'default_locale' => 'en_GB',
            ])
            ->assertStatus(200);

        $this->actingAs($user)
            ->putJson('/settings/security', [
                'two_factor_required' => false,
                'session_timeout_minutes' => 120,
                'max_login_attempts' => 5,
                'password_expiry_days' => null,
            ])
            ->assertStatus(200);
    });
});

describe('singleton scoping', function () {
    test('repeated updates never create a second settings row', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)->putJson('/settings/general', [
            'site_name' => 'First Update',
            'support_email' => 'help@mattyeend.co.uk',
            'timezone' => 'Europe/London',
            'date_format' => 'd/m/Y',
        ])->assertStatus(200);

        $this->actingAs($superAdmin)->putJson('/settings/general', [
            'site_name' => 'Second Update',
            'support_email' => 'help@mattyeend.co.uk',
            'timezone' => 'Europe/London',
            'date_format' => 'd/m/Y',
        ])->assertStatus(200);

        $this->assertDatabaseCount('settings', 1);
    });
});

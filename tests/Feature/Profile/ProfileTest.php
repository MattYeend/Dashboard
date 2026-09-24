<?php

use App\Models\Log;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

describe('show', function () {
    test('authenticated user can view their own profile', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/profile')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Show')
                ->has('user')
            );
    });

    test('unauthenticated user cannot view the profile page', function () {
        $this->get('/profile')
            ->assertRedirect('/login');
    });

    test('user without the view own profile permission is forbidden', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/profile')
            ->assertStatus(403);
    });
});

describe('edit', function () {
    test('authenticated user can view the profile edit form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/profile/edit')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Edit')
                ->has('user')
                ->has('sessions')
                ->has('canManageTokens')
            );
    });

    test('token management data is hidden for a role without api key permissions', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/profile/edit')
            ->assertInertia(fn (Assert $page) => $page
                ->where('canManageTokens', false)
                ->where('tokens', [])
            );
    });

    test('token management data is shown for an admin', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get('/profile/edit')
            ->assertInertia(fn (Assert $page) => $page
                ->where('canManageTokens', true)
            );
    });
});

describe('update', function () {
    test('updates the name and email and clears verification when the email changes', function () {
        Notification::fake();
        $user = $this->normalUser();

        $this->actingAs($user)
            ->put('/profile', ['name' => 'New Name', 'email' => 'new@example.com'])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        expect($user->name)->toBe('New Name')
            ->and($user->email)->toBe('new@example.com')
            ->and($user->email_verified_at)->toBeNull();
    });

    test('ignores attempts to change the role through the profile endpoint', function () {
        $user = $this->normalUser();
        $originalRole = $user->role;

        $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'super_admin',
            ]);

        expect($user->refresh()->role)->toBe($originalRole);
    });

    test('logs profile updates with actor id', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->put('/profile', ['name' => 'Logged Name', 'email' => $user->email])
            ->assertRedirect(route('profile.edit'));

        $log = Log::query()
            ->where('action_id', Log::ACTION_PROFILE_UPDATED)
            ->where('logged_in_user_id', $user->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });

    test('user without the edit own profile permission is forbidden', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->put('/profile', ['name' => 'Blocked', 'email' => 'blocked@example.com'])
            ->assertStatus(403);
    });
});

describe('password update', function () {
    test('requires the current password to change the password', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'wrong-password',
                'password' => 'A-Very-Long-Passw0rd!',
                'password_confirmation' => 'A-Very-Long-Passw0rd!',
            ])
            ->assertSessionHasErrors('current_password');
    });

    test('logs the password change without storing the password itself', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'password',
                'password' => 'A-Very-Long-Passw0rd!',
                'password_confirmation' => 'A-Very-Long-Passw0rd!',
            ])
            ->assertRedirect(route('profile.edit'));

        $log = Log::query()
            ->where('action_id', Log::ACTION_PASSWORD_CHANGED)
            ->where('logged_in_user_id', $user->id)
            ->first();

        expect($log)->not->toBeNull();
        expect(json_encode($log->data))->not->toContain('A-Very-Long-Passw0rd!');
    });
});

describe('sessions', function () {
    test('never returns session identifiers to the browser', function () {
        config(['session.driver' => 'database']);
        $user = $this->normalUser();

        DB::table('sessions')->insert([
            'id' => 'secret-session-identifier',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Pest',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($user)
            ->get('/profile/edit')
            ->assertOk()
            ->assertDontSee('secret-session-identifier');
    });

    test('revoking other sessions requires the current password', function () {
        config(['session.driver' => 'database']);
        $user = $this->normalUser();

        $this->actingAs($user)
            ->delete('/profile/sessions', ['password' => 'wrong-password'])
            ->assertSessionHasErrors('password');
    });
});

describe('tokens', function () {
    test('forbids token creation for a role without the create api keys permission', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->post('/profile/tokens', [
                'name' => 'CLI',
                'abilities' => ['records:read'],
                'expires_in_days' => 30,
            ])
            ->assertForbidden();
    });

    test('allows an admin to create and then revoke their own token', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post('/profile/tokens', [
                'name' => 'CLI',
                'abilities' => ['records:read'],
                'expires_in_days' => 30,
            ])
            ->assertRedirect(route('profile.edit'));

        $token = $admin->tokens()->sole();

        $this->actingAs($admin)
            ->delete("/profile/tokens/{$token->id}")
            ->assertRedirect(route('profile.edit'));

        expect($admin->tokens()->count())->toBe(0);
    });

    test('forbids revoking a token that belongs to someone else, even for a super admin', function () {
        $owner = $this->adminUser();
        $token = $owner->createToken('CLI', ['records:read']);

        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->delete("/profile/tokens/{$token->accessToken->id}")
            ->assertNotFound();
    });
});

describe('destroy', function () {
    test('requires the password to delete the account', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->delete('/profile', ['password' => 'wrong-password'])
            ->assertSessionHasErrors('password');

        expect($user->fresh())->not->toBeNull();
    });

    test('refuses to delete the last super admin', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->delete('/profile', ['password' => 'password'])
            ->assertSessionHasErrors('password');

        expect(User::query()->whereKey($superAdmin->id)->exists())->toBeTrue();
    });
});
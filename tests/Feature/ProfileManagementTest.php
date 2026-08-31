<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
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

describe('show', function () {
    test('authenticated user with permission can view their own profile', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Show')
                ->where('isOwnProfile', true)
                ->where('user.id', $user->id)
            );
    });

    test('unauthenticated user cannot view the profile page', function () {
        $this->get(route('profile.show'))
            ->assertRedirect('/login');
    });

    test('user without permission cannot view their own profile', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertStatus(403);
    });
});

describe('show other', function () {
    test('an admin with permission can view another user\'s profile read-only', function () {
        $admin = $this->adminUser();
        $other = $this->normalUser();

        $this->actingAs($admin)
            ->get(route('profile.show-other', $other))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Show')
                ->where('isOwnProfile', false)
                ->where('user.id', $other->id)
            );
    });

    test('a normal user cannot view another user\'s profile', function () {
        $user = $this->normalUser();
        $other = $this->normalUser();

        $this->actingAs($user)
            ->get(route('profile.show-other', $other))
            ->assertStatus(403);
    });

    test('unauthenticated user cannot view another user\'s profile', function () {
        $other = $this->normalUser();

        $this->get(route('profile.show-other', $other))
            ->assertRedirect('/login');
    });

    test('show other returns 404 for a non-existent user', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('profile.show-other', 999999))
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view the edit profile page', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Profile/Edit'));
    });

    test('user without permission cannot view the edit profile page', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('a user with permission can update their own profile', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => $user->email,
            ])
            ->assertRedirect(route('profile.edit'));

        expect($user->fresh()->name)->toBe('Updated Name');
    });

    test('a user without permission cannot update their own profile', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => $user->email,
            ])
            ->assertStatus(403);

        expect($user->fresh()->name)->not->toBe('Updated Name');
    });

    test('update fails validation when email is missing', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => '',
            ])
            ->assertSessionHasErrors(['email']);
    });

    test('a user cannot update another user\'s profile via this endpoint', function () {
        $user = $this->normalUser();
        $other = $this->normalUser();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Attempted Takeover',
                'email' => $user->email,
            ])
            ->assertRedirect(route('profile.edit'));

        expect($other->fresh()->name)->not->toBe('Attempted Takeover');
    });
});

describe('password update', function () {
    test('a user with permission can change their own password', function () {
        $user = $this->normalUser();
        $user->forceFill(['password' => Hash::make('old-password')])->save();

        $this->actingAs($user)
            ->patch(route('profile.update-password'), [
                'current_password' => 'old-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('profile.edit'));

        expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
    });

    test('password update rejects an incorrect current password', function () {
        $user = $this->normalUser();
        $user->forceFill(['password' => Hash::make('old-password')])->save();

        $this->actingAs($user)
            ->patch(route('profile.update-password'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasErrors('current_password');
    });

    test('a user without permission cannot change their own password', function () {
        $user = $this->userWithNoPermissions();
        $user->forceFill(['password' => Hash::make('old-password')])->save();

        $this->actingAs($user)
            ->patch(route('profile.update-password'), [
                'current_password' => 'old-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertStatus(403);
    });
});

describe('destroy', function () {
    test('a user with permission can delete their own profile', function () {
        $user = $this->normalUser();
        $user->forceFill(['password' => Hash::make('correct-password')])->save();

        $this->actingAs($user)
            ->delete(route('profile.destroy'), [
                'password' => 'correct-password',
            ])
            ->assertRedirect('/');

        expect(User::withTrashed()->find($user->id)->trashed())->toBeTrue();
    });

    test('a user without permission cannot delete their own profile', function () {
        $user = $this->userWithNoPermissions();
        $user->forceFill(['password' => Hash::make('correct-password')])->save();

        $this->actingAs($user)
            ->delete(route('profile.destroy'), [
                'password' => 'correct-password',
            ])
            ->assertStatus(403);

        expect($user->fresh()->trashed())->toBeFalse();
    });

    test('a user with view other profiles permission still cannot delete another user\'s profile', function () {
        $admin = $this->adminUser();
        $admin->forceFill(['password' => Hash::make('correct-password')])->save();
        $other = $this->normalUser();

        $this->actingAs($admin)
            ->delete(route('profile.destroy'), [
                'password' => 'correct-password',
            ])
            ->assertRedirect('/');

        expect($other->fresh()->trashed())->toBeFalse();
    });
});

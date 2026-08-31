<?php

use App\Policies\UserPolicy;
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

describe('viewOwnProfile', function () {
    test('a normal user can view their own profile', function () {
        $user = $this->normalUser();
        $policy = app(UserPolicy::class);

        expect($policy->viewOwnProfile($user, $user))->toBeTrue();
    });

    test('a user without permissions cannot view their own profile', function () {
        $user = $this->userWithNoPermissions();
        $policy = app(UserPolicy::class);

        expect($policy->viewOwnProfile($user, $user))->toBeFalse();
    });

    test('a user cannot view another user\'s profile via viewOwnProfile', function () {
        $user = $this->normalUser();
        $other = $this->normalUser();
        $policy = app(UserPolicy::class);

        expect($policy->viewOwnProfile($user, $other))->toBeFalse();
    });
});

describe('editOwnProfile', function () {
    test('a normal user can edit their own profile', function () {
        $user = $this->normalUser();
        $policy = app(UserPolicy::class);

        expect($policy->editOwnProfile($user, $user))->toBeTrue();
    });

    test('a user without permissions cannot edit their own profile', function () {
        $user = $this->userWithNoPermissions();
        $policy = app(UserPolicy::class);

        expect($policy->editOwnProfile($user, $user))->toBeFalse();
    });

    test('a user cannot edit another user\'s profile even with edit own profile permission', function () {
        $user = $this->normalUser();
        $other = $this->normalUser();
        $policy = app(UserPolicy::class);

        expect($policy->editOwnProfile($user, $other))->toBeFalse();
    });
});

describe('deleteOwnProfile', function () {
    test('a normal user can delete their own profile', function () {
        $user = $this->normalUser();
        $policy = app(UserPolicy::class);

        expect($policy->deleteOwnProfile($user, $user))->toBeTrue();
    });

    test('a user without permissions cannot delete their own profile', function () {
        $user = $this->userWithNoPermissions();
        $policy = app(UserPolicy::class);

        expect($policy->deleteOwnProfile($user, $user))->toBeFalse();
    });

    test('a user cannot delete another user\'s profile even with delete own profile permission', function () {
        $user = $this->normalUser();
        $other = $this->normalUser();
        $policy = app(UserPolicy::class);

        expect($policy->deleteOwnProfile($user, $other))->toBeFalse();
    });
});

describe('changeOwnPassword', function () {
    test('a normal user can change their own password', function () {
        $user = $this->normalUser();
        $policy = app(UserPolicy::class);

        expect($policy->changeOwnPassword($user, $user))->toBeTrue();
    });

    test('a user without permissions cannot change their own password', function () {
        $user = $this->userWithNoPermissions();
        $policy = app(UserPolicy::class);

        expect($policy->changeOwnPassword($user, $user))->toBeFalse();
    });

    test('a user cannot change another user\'s password even with change own password permission', function () {
        $user = $this->normalUser();
        $other = $this->normalUser();
        $policy = app(UserPolicy::class);

        expect($policy->changeOwnPassword($user, $other))->toBeFalse();
    });
});

describe('viewOtherProfile', function () {
    test('an admin can view another user\'s profile', function () {
        $admin = $this->adminUser();
        $other = $this->normalUser();
        $policy = app(UserPolicy::class);

        expect($policy->viewOtherProfile($admin, $other))->toBeTrue();
    });

    test('a normal user cannot view another user\'s profile', function () {
        $user = $this->normalUser();
        $other = $this->normalUser();
        $policy = app(UserPolicy::class);

        expect($policy->viewOtherProfile($user, $other))->toBeFalse();
    });

    test('a user without permissions cannot view another user\'s profile', function () {
        $user = $this->userWithNoPermissions();
        $other = $this->normalUser();
        $policy = app(UserPolicy::class);

        expect($policy->viewOtherProfile($user, $other))->toBeFalse();
    });

    test('an admin cannot use viewOtherProfile against themselves', function () {
        $admin = $this->adminUser();
        $policy = app(UserPolicy::class);

        expect($policy->viewOtherProfile($admin, $admin))->toBeFalse();
    });
});

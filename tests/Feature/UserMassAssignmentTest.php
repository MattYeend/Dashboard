<?php

use App\Models\User;
use App\Services\Users\DataPreparationService;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('role and audit column protection', function () {
    test('throws when mass assigning role and audit columns directly', function () {
        expect(fn () => User::create([
            'name' => 'Test User',
            'email' => 'test.user@example.com',
            'password' => 'password',
            'role' => 'super_admin',
            'created_by' => 999,
            'updated_by' => 999,
            'deleted_by' => 999,
            'restored_by' => 999,
            'restored_at' => now(),
        ]))->toThrow(MassAssignmentException::class);
    });

    test('data preparation service strips role and audit columns before creation', function () {
        $prepared = app(DataPreparationService::class)->prepareForCreation([
            'name' => 'Test User',
            'email' => 'test.user@example.com',
            'password' => 'password',
            'role' => 'super_admin',
            'created_by' => 999,
            'updated_by' => 999,
            'deleted_by' => 999,
            'restored_by' => 999,
            'restored_at' => now(),
        ]);

        expect($prepared)->not->toHaveKeys([
            'role',
            'created_by',
            'updated_by',
            'deleted_by',
            'restored_by',
            'restored_at',
        ]);

        $user = User::create($prepared);

        expect($user->role)->not->toBe('super_admin');
        expect($user->created_by)->toBeNull();
        expect($user->updated_by)->toBeNull();
        expect($user->deleted_by)->toBeNull();
        expect($user->restored_by)->toBeNull();
        expect($user->restored_at)->toBeNull();
    });
});

describe('meta assignment', function () {
    test('allows meta to be mass assigned', function () {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'meta.user@example.com',
            'password' => 'password',
            'meta' => ['theme' => 'dark'],
        ]);

        expect($user->meta)->toBe(['theme' => 'dark']);
    });
});

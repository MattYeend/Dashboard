<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ignores role and audit columns when mass assigning via create', function () {
    $user = User::create([
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

    expect($user->role)->not->toBe('super_admin');
    expect($user->created_by)->toBeNull();
    expect($user->updated_by)->toBeNull();
    expect($user->deleted_by)->toBeNull();
    expect($user->restored_by)->toBeNull();
    expect($user->restored_at)->toBeNull();
});

test('allows meta to be mass assigned', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'meta.user@example.com',
        'password' => 'password',
        'meta' => ['theme' => 'dark'],
    ]);

    expect($user->meta)->toBe(['theme' => 'dark']);
});

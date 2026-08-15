<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an unverified user cannot receive a token', function () {
    User::factory()->unverified()->create([
        'email' => 'unverified@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->postJson('/api/login', [
        'email' => 'unverified@example.com',
        'password' => 'password',
        'device_name' => 'test-device',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    $this->assertGuest();
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('an unverified user cannot receive a session', function () {
    User::factory()->unverified()->create([
        'email' => 'unverified-spa@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->postJson('/api/login', [
        'email' => 'unverified-spa@example.com',
        'password' => 'password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    $this->assertGuest();
});

test('a verified user can receive a token', function () {
    User::factory()->create([
        'email' => 'verified@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    $this->postJson('/api/login', [
        'email' => 'verified@example.com',
        'password' => 'password',
        'device_name' => 'test-device',
    ])
        ->assertOk()
        ->assertJsonStructure(['user', 'token']);

    $this->assertDatabaseCount('personal_access_tokens', 1);
});

test('a verified user can receive a session', function () {
    $user = User::factory()->create([
        'email' => 'verified-spa@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    $this->postJson('/api/login', [
        'email' => 'verified-spa@example.com',
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonMissingPath('token');

    $this->assertAuthenticatedAs($user);
});

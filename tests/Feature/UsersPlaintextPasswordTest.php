<?php

use App\Mail\WelcomeEmail;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('does not leak a plaintext password when creating a user', function () {
    setPermissionsTeamId(1);
    Mail::fake();
    Notification::fake();

    $actor = User::factory()->create();
    $actor->assignRoles('Super Admin');

    $response = $this->actingAs($actor)->post(route('users.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'Whatever-Temp-Value-1',
        'password_confirmation' => 'Whatever-Temp-Value-1',
        'role' => 'user',
    ]);

    $response->assertSessionHasNoErrors();

    $newUser = User::where('email', 'jane@example.com')->firstOrFail();

    Mail::assertQueued(WelcomeEmail::class, function (WelcomeEmail $mail) {
        return ! property_exists($mail, 'password');
    });

    Notification::assertSentTo($newUser, ResetPassword::class);

    expect(property_exists($newUser, 'plainPassword'))->toBeFalse();
});

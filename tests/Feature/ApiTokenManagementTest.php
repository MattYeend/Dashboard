<?php

use App\Models\Log;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Sanctum\PersonalAccessToken;
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
    test('authenticated user with permission can list their own tokens', function () {
        $superAdmin = $this->superAdminUser();

        $superAdmin->createToken('existing token', ['tasks:read']);

        $this->actingAs($superAdmin)
            ->get('/api-tokens')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('ApiTokens/Index')
                ->has('tokens', 1)
                ->has('abilities')
            );
    });

    test('index only returns the authenticated user\'s own tokens', function () {
        $superAdmin = $this->superAdminUser();
        $otherUser = $this->superAdminUser();

        $superAdmin->createToken('mine', ['tasks:read']);
        $otherUser->createToken('not mine', ['tasks:read']);

        $this->actingAs($superAdmin)
            ->get('/api-tokens')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('ApiTokens/Index')
                ->has('tokens', 1)
            );
    });

    test('unauthenticated user cannot list tokens', function () {
        $this->get('/api-tokens')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list tokens', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/api-tokens')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a token', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->post('/api-tokens', [
                'name' => 'Zapier integration',
                'abilities' => ['tasks:read'],
            ])
            ->assertRedirect('/api-tokens')
            ->assertSessionHas('plainTextToken');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $superAdmin->id,
            'name' => 'Zapier integration',
        ]);
    });

    test('user without permission cannot create a token', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->post('/api-tokens', [
                'name' => 'Zapier integration',
                'abilities' => ['tasks:read'],
            ])
            ->assertStatus(403);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    });

    test('store fails validation when name is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->post('/api-tokens', [
                'abilities' => ['tasks:read'],
            ])
            ->assertSessionHasErrors(['name']);
    });

    test('store fails validation when abilities is empty', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->post('/api-tokens', [
                'name' => 'Zapier integration',
                'abilities' => [],
            ])
            ->assertSessionHasErrors(['abilities']);
    });

    test('store fails validation when an ability is not a recognised value', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->post('/api-tokens', [
                'name' => 'Zapier integration',
                'abilities' => ['not-a-real-ability'],
            ])
            ->assertSessionHasErrors(['abilities.0']);
    });

    test('store fails validation when expires_at is in the past', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->post('/api-tokens', [
                'name' => 'Zapier integration',
                'abilities' => ['tasks:read'],
                'expires_at' => now()->subDay()->toDateTimeString(),
            ])
            ->assertSessionHasErrors(['expires_at']);
    });

    test('logs token creation with actor id', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->post('/api-tokens', [
                'name' => 'Zapier integration',
                'abilities' => ['tasks:read'],
            ])
            ->assertRedirect('/api-tokens');

        $log = Log::query()
            ->where('action_id', Log::ACTION_CREATE_API_TOKEN)
            ->where('logged_in_user_id', $superAdmin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->related_to_user_id)->toBe($superAdmin->id)
            ->and($log->data)->toHaveKeys(['name', 'abilities']);
    });
});

describe('update', function () {
    test('owner with permission can update their own token', function () {
        $superAdmin = $this->superAdminUser();

        $token = $superAdmin->createToken('old name', ['tasks:read'])->accessToken;

        $this->actingAs($superAdmin)
            ->put("/api-tokens/{$token->id}", [
                'name' => 'new name',
            ])
            ->assertRedirect('/api-tokens');

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->id,
            'name' => 'new name',
        ]);
    });

    test('a non-owner with permission cannot update someone else\'s token', function () {
        $owner = $this->superAdminUser();
        $intruder = $this->superAdminUser();

        $token = $owner->createToken('old name', ['tasks:read'])->accessToken;

        $this->actingAs($intruder)
            ->put("/api-tokens/{$token->id}", [
                'name' => 'hijacked',
            ])
            ->assertStatus(403);

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->id,
            'name' => 'old name',
        ]);
    });

    test('user without permission cannot update their own token', function () {
        $user = $this->userWithNoPermissions();

        $token = $user->createToken('old name', ['tasks:read'])->accessToken;

        $this->actingAs($user)
            ->put("/api-tokens/{$token->id}", [
                'name' => 'new name',
            ])
            ->assertStatus(403);
    });

    test('update fails validation when an ability is not a recognised value', function () {
        $superAdmin = $this->superAdminUser();

        $token = $superAdmin->createToken('old name', ['tasks:read'])->accessToken;

        $this->actingAs($superAdmin)
            ->put("/api-tokens/{$token->id}", [
                'abilities' => ['not-a-real-ability'],
            ])
            ->assertSessionHasErrors(['abilities.0']);
    });

    test('logs token updates with actor id', function () {
        $superAdmin = $this->superAdminUser();

        $token = $superAdmin->createToken('old name', ['tasks:read'])->accessToken;

        $this->actingAs($superAdmin)
            ->put("/api-tokens/{$token->id}", [
                'name' => 'new name',
            ])
            ->assertRedirect('/api-tokens');

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_API_TOKEN)
            ->where('logged_in_user_id', $superAdmin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->related_to_user_id)->toBe($superAdmin->id)
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('owner with permission can revoke their own token', function () {
        $superAdmin = $this->superAdminUser();

        $token = $superAdmin->createToken('to revoke', ['tasks:read'])->accessToken;

        $this->actingAs($superAdmin)
            ->delete("/api-tokens/{$token->id}")
            ->assertRedirect('/api-tokens');

        expect(PersonalAccessToken::find($token->id))->toBeNull();
    });

    test('revoking a token immediately invalidates it for API requests', function () {
        $superAdmin = $this->superAdminUser();

        $newToken = $superAdmin->createToken('to revoke', ['tasks:read']);
        $plainTextToken = $newToken->plainTextToken;

        $this->withHeaders(['Authorization' => "Bearer {$plainTextToken}"])
            ->getJson('/api/user')
            ->assertStatus(200);

        $this->actingAs($superAdmin)
            ->delete("/api-tokens/{$newToken->accessToken->id}")
            ->assertRedirect('/api-tokens');

        // actingAs() leaves the web guard authenticated for the rest of this
        // test method. Left alone, Sanctum's guard falls back to that session
        // user on the next request rather than evaluating the (now revoked)
        // bearer token — forgetting the guards forces a genuine re-check.
        $this->app['auth']->forgetGuards();

        $this->withHeaders(['Authorization' => "Bearer {$plainTextToken}"])
            ->getJson('/api/user')
            ->assertStatus(401);
    });

    test('a non-owner with permission cannot revoke someone else\'s token', function () {
        $owner = $this->superAdminUser();
        $intruder = $this->superAdminUser();

        $token = $owner->createToken('to revoke', ['tasks:read'])->accessToken;

        $this->actingAs($intruder)
            ->delete("/api-tokens/{$token->id}")
            ->assertStatus(403);

        expect(PersonalAccessToken::find($token->id))->not->toBeNull();
    });

    test('user without permission cannot revoke their own token', function () {
        $user = $this->userWithNoPermissions();

        $token = $user->createToken('to revoke', ['tasks:read'])->accessToken;

        $this->actingAs($user)
            ->delete("/api-tokens/{$token->id}")
            ->assertStatus(403);

        expect(PersonalAccessToken::find($token->id))->not->toBeNull();
    });

    test('destroy returns 404 for a non-existent token', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->delete('/api-tokens/99999')
            ->assertStatus(404);
    });

    test('logs token revocation with actor id', function () {
        $superAdmin = $this->superAdminUser();

        $token = $superAdmin->createToken('to revoke', ['tasks:read'])->accessToken;

        $this->actingAs($superAdmin)
            ->delete("/api-tokens/{$token->id}")
            ->assertRedirect('/api-tokens');

        $log = Log::query()
            ->where('action_id', Log::ACTION_REVOKE_API_TOKEN)
            ->where('logged_in_user_id', $superAdmin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->related_to_user_id)->toBe($superAdmin->id)
            ->and($log->data)->toHaveKeys(['name', 'abilities']);
    });
});

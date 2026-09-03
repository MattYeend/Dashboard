<?php

use App\Models\Log;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
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

describe('index', function () {
    test('authenticated user with permission can list organisations', function () {
        $superAdmin = $this->superAdminUser();

        Organisation::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/organisations')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Organisations/Index')
                ->has('organisations')
            );
    });

    test('unauthenticated user cannot list organisations', function () {
        $this->get('/organisations')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list organisations', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/organisations')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/organisations/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Organisations/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/organisations/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/organisations/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create an organisation', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/organisations', ['name' => 'Yeend Web Development'])
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Yeend Web Development']);

        $this->assertDatabaseHas('organisations', [
            'name' => 'Yeend Web Development',
            'slug' => 'yeend-web-development',
        ]);
    });

    test('creator is automatically attached as a member', function () {
        $superAdmin = $this->superAdminUser();

        $response = $this->actingAs($superAdmin)
            ->postJson('/organisations', ['name' => 'Bangor Digital Services']);

        $organisationId = $response->json('id');

        $this->assertDatabaseHas('organisation_user', [
            'organisation_id' => $organisationId,
            'user_id' => $superAdmin->id,
        ]);
    });

    test('user without permission cannot create an organisation', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/organisations', ['name' => 'Blocked Org'])
            ->assertStatus(403);
    });

    test('store fails validation when name is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/organisations', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('store fails validation when slug already exists', function () {
        $superAdmin = $this->superAdminUser();

        Organisation::factory()->create(['slug' => 'duplicate-org']);

        $this->actingAs($superAdmin)
            ->postJson('/organisations', [
                'name' => 'Duplicate Org',
                'slug' => 'duplicate-org',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    });

    test('logs organisation creation with actor id', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/organisations', ['name' => 'Leicester Trade Supplies'])
            ->assertStatus(201);

        $log = Log::query()
            ->where('action_id', Log::ACTION_CREATE_ORGANISATION)
            ->where('logged_in_user_id', $superAdmin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKey('after');
    });
});

describe('show', function () {
    test('super admin can view an organisation they are not a member of', function () {
        $superAdmin = $this->superAdminUser();

        $organisation = Organisation::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/organisations/{$organisation->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Organisations/Show')
                ->has('organisation')
            );
    });

    test('member can view their own organisation', function () {
        $admin = $this->adminUser();

        $organisation = Organisation::factory()->create();
        $organisation->users()->attach($admin->id);

        $this->actingAs($admin)
            ->get("/organisations/{$organisation->id}")
            ->assertStatus(200);
    });

    test('non-member without super admin role cannot view an organisation', function () {
        $admin = $this->adminUser();

        $organisation = Organisation::factory()->create();

        $this->actingAs($admin)
            ->get("/organisations/{$organisation->id}")
            ->assertStatus(403);
    });

    test('unauthenticated user cannot view an organisation', function () {
        $organisation = Organisation::factory()->create();

        $this->get("/organisations/{$organisation->id}")
            ->assertRedirect('/login');
    });

    test('show returns 404 for a non-existent organisation', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/organisations/99999')
            ->assertStatus(404);
    });
});

describe('update', function () {
    test('authenticated user with permission can update an organisation', function () {
        $superAdmin = $this->superAdminUser();

        $organisation = Organisation::factory()->create(['name' => 'Old Name']);

        $this->actingAs($superAdmin)
            ->putJson("/organisations/{$organisation->id}", ['name' => 'New Name'])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);

        $this->assertDatabaseHas('organisations', [
            'id' => $organisation->id,
            'name' => 'New Name',
        ]);
    });

    test('non-member without super admin role cannot update an organisation', function () {
        $admin = $this->adminUser();

        $organisation = Organisation::factory()->create();

        $this->actingAs($admin)
            ->putJson("/organisations/{$organisation->id}", ['name' => 'Blocked'])
            ->assertStatus(403);
    });

    test('logs organisation updates with actor id', function () {
        $superAdmin = $this->superAdminUser();

        $organisation = Organisation::factory()->create(['name' => 'Old Name']);

        $this->actingAs($superAdmin)
            ->putJson("/organisations/{$organisation->id}", ['name' => 'New Name'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_ORGANISATION)
            ->where('logged_in_user_id', $superAdmin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete an organisation', function () {
        $superAdmin = $this->superAdminUser();

        $organisation = Organisation::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/organisations/{$organisation->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('organisations', ['id' => $organisation->id]);
    });

    test('user without permission cannot soft delete an organisation', function () {
        $user = $this->normalUser();

        $organisation = Organisation::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/organisations/{$organisation->id}")
            ->assertStatus(403);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted organisation', function () {
        $superAdmin = $this->superAdminUser();

        $organisation = Organisation::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/organisations/{$organisation->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('organisations', [
            'id' => $organisation->id,
            'deleted_at' => null,
        ]);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete an organisation', function () {
        $superAdmin = $this->superAdminUser();

        $organisation = Organisation::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/organisations/{$organisation->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('organisations', ['id' => $organisation->id]);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete organisations', function () {
        $superAdmin = $this->superAdminUser();

        $organisations = Organisation::factory()->count(3)->create();
        $ids = $organisations->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/organisations/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('organisations', ['id' => $id]);
        }
    });
});

describe('switching', function () {
    test('member can switch to an organisation they belong to', function () {
        $user = $this->normalUser();

        $organisation = Organisation::factory()->create();
        $organisation->users()->attach($user->id);

        $this->actingAs($user)
            ->post("/organisations/{$organisation->id}/switch")
            ->assertRedirect();

        $this->assertEquals(
            $organisation->id,
            session('current_organisation_id')
        );
    });

    test('user cannot switch to an organisation they do not belong to', function () {
        $user = $this->normalUser();

        $organisation = Organisation::factory()->create();

        $this->actingAs($user)
            ->post("/organisations/{$organisation->id}/switch")
            ->assertStatus(403);
    });

    test('unauthenticated user cannot switch organisation', function () {
        $organisation = Organisation::factory()->create();

        $this->post("/organisations/{$organisation->id}/switch")
            ->assertRedirect('/login');
    });
});

describe('no current organisation', function () {
    test('a user with no organisation memberships is redirected to select one', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/organisations/select');
    });
});

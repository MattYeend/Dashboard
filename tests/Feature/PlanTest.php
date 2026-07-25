<?php

use App\Models\Log;
use App\Models\Plan;
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
    test('authenticated user with permission can list plans', function () {
        $superAdmin = $this->superAdminUser();

        Plan::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/plans')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Plans/Index')
                ->has('plans')
            );
    });

    test('unauthenticated user cannot list plans', function () {
        $this->get('/plans')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list plans', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/plans')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/plans/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Plans/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/plans/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/plans/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a plan', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'name' => 'Starter',
            'description' => 'Essential features for small teams.',
            'price_per_user_per_month' => 2500,
            'is_active' => true,
        ];

        $this->actingAs($superAdmin)
            ->postJson('/plans', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Starter']);

        $this->assertDatabaseHas('plans', [
            'name' => 'Starter',
            'slug' => 'starter',
            'price_per_user_per_month' => 2500,
        ]);
    });

    test('user without permission cannot create a plan', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/plans', [
                'name' => 'Starter',
                'price_per_user_per_month' => 2500,
            ])
            ->assertStatus(403);
    });

    test('store fails validation when name is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/plans', [
                'price_per_user_per_month' => 2500,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('store fails validation when name already exists', function () {
        $superAdmin = $this->superAdminUser();

        Plan::factory()->create(['name' => 'Starter']);

        $this->actingAs($superAdmin)
            ->postJson('/plans', [
                'name' => 'Starter',
                'price_per_user_per_month' => 2500,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('store fails validation when price_per_user_per_month is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/plans', [
                'name' => 'Starter',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['price_per_user_per_month']);
    });

    test('store fails validation when price_per_user_per_month is negative', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/plans', [
                'name' => 'Starter',
                'price_per_user_per_month' => -100,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['price_per_user_per_month']);
    });

    test('store fails validation when price_per_user_per_month is not an integer', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/plans', [
                'name' => 'Starter',
                'price_per_user_per_month' => 29.99,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['price_per_user_per_month']);
    });

    test('store fails validation when slug format is invalid', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/plans', [
                'name' => 'Starter',
                'slug' => 'Not A Valid Slug!',
                'price_per_user_per_month' => 2500,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/plans', [
                'name' => 'Basic',
                'price_per_user_per_month' => 1000,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('plans', [
            'name' => 'Basic',
            'description' => null,
        ]);
    });

    test('store defaults is_active to true when omitted', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/plans', [
                'name' => 'Basic',
                'price_per_user_per_month' => 1000,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('plans', [
            'name' => 'Basic',
            'is_active' => true,
        ]);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a plan', function () {
        $superAdmin = $this->superAdminUser();

        $plan = Plan::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/plans/{$plan->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Plans/Show')
                ->has('plan')
            );
    });

    test('unauthenticated user cannot view a plan', function () {
        $plan = Plan::factory()->create();

        $this->get("/plans/{$plan->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a plan', function () {
        $user = $this->userWithNoPermissions();

        $plan = Plan::factory()->create();

        $this->actingAs($user)
            ->get("/plans/{$plan->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent plan', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/plans/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $plan = Plan::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/plans/{$plan->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Plans/Edit')
                ->has('plan')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $plan = Plan::factory()->create();

        $this->get("/plans/{$plan->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $plan = Plan::factory()->create();

        $this->actingAs($user)
            ->get("/plans/{$plan->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a plan', function () {
        $superAdmin = $this->superAdminUser();

        $plan = Plan::factory()->create(['name' => 'Starter']);

        $this->actingAs($superAdmin)
            ->putJson("/plans/{$plan->id}", ['name' => 'Starter Plus'])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Starter Plus']);

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'name' => 'Starter Plus',
        ]);
    });

    test('patch verb also updates a plan', function () {
        $superAdmin = $this->superAdminUser();

        $plan = Plan::factory()->create(['price_per_user_per_month' => 2500]);

        $this->actingAs($superAdmin)
            ->patchJson("/plans/{$plan->id}", ['price_per_user_per_month' => 3000])
            ->assertStatus(200)
            ->assertJsonFragment(['price_per_user_per_month' => 3000]);
    });

    test('user without permission cannot update a plan', function () {
        $user = $this->normalUser();

        $plan = Plan::factory()->create();

        $this->actingAs($user)
            ->putJson("/plans/{$plan->id}", ['name' => 'New Name'])
            ->assertStatus(403);
    });

    test('update fails validation when name already used by another plan', function () {
        $superAdmin = $this->superAdminUser();

        Plan::factory()->create(['name' => 'Professional']);
        $plan = Plan::factory()->create(['name' => 'Starter']);

        $this->actingAs($superAdmin)
            ->putJson("/plans/{$plan->id}", ['name' => 'Professional'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('update allows a plan to keep its own name unchanged', function () {
        $superAdmin = $this->superAdminUser();

        $plan = Plan::factory()->create(['name' => 'Starter']);

        $this->actingAs($superAdmin)
            ->putJson("/plans/{$plan->id}", ['name' => 'Starter'])
            ->assertStatus(200);
    });

    test('update fails validation when price_per_user_per_month is negative', function () {
        $superAdmin = $this->superAdminUser();

        $plan = Plan::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/plans/{$plan->id}", ['price_per_user_per_month' => -50])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['price_per_user_per_month']);
    });

    test('description can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();

        $plan = Plan::factory()->create([
            'description' => 'Original description.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/plans/{$plan->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'description' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $plan = Plan::factory()->create([
            'description' => 'Original description.',
            'price_per_user_per_month' => 2500,
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/plans/{$plan->id}", [
                'name' => 'Updated Name',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'name' => 'Updated Name',
            'description' => 'Original description.',
            'price_per_user_per_month' => 2500,
        ]);
    });

    test('is_active can be toggled on update', function () {
        $superAdmin = $this->superAdminUser();

        $plan = Plan::factory()->create(['is_active' => true]);

        $this->actingAs($superAdmin)
            ->putJson("/plans/{$plan->id}", ['is_active' => false])
            ->assertStatus(200);

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'is_active' => false,
        ]);
    });

    test('logs plan updates with actor id', function () {
        $actor = $this->adminUser();

        $plan = Plan::factory()->create(['name' => 'Old Name']);

        $this->actingAs($actor)
            ->putJson("/plans/{$plan->id}", ['name' => 'New Name'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_PLAN)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a plan', function () {
        $superAdmin = $this->superAdminUser();

        $plan = Plan::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/plans/{$plan->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('plans', ['id' => $plan->id]);
    });

    test('user without permission cannot soft delete a plan', function () {
        $user = $this->normalUser();

        $plan = Plan::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/plans/{$plan->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent plan', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/plans/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted plan', function () {
        $superAdmin = $this->superAdminUser();

        $plan = Plan::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/plans/{$plan->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a plan', function () {
        $user = $this->normalUser();

        $plan = Plan::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/plans/{$plan->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a plan that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $plan = Plan::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/plans/{$plan->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a plan', function () {
        $superAdmin = $this->superAdminUser();

        $plan = Plan::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/plans/{$plan->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
    });

    test('user without permission cannot force delete a plan', function () {
        $user = $this->normalUser();

        $plan = Plan::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/plans/{$plan->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a plan that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $plan = Plan::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/plans/{$plan->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete plans', function () {
        $superAdmin = $this->superAdminUser();

        $plans = Plan::factory()->count(3)->create();
        $ids = $plans->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/plans/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('plans', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/plans/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/plans/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete plans', function () {
        $user = $this->normalUser();

        $plans = Plan::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/plans/bulk/delete', [
                'ids' => $plans->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore plans', function () {
        $superAdmin = $this->superAdminUser();

        $plans = Plan::factory()->count(3)->deleted()->create();
        $ids = $plans->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/plans/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('plans', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/plans/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/plans/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore plans', function () {
        $user = $this->normalUser();

        $plans = Plan::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/plans/bulk/restore', [
                'ids' => $plans->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted plans', function () {
        $superAdmin = $this->superAdminUser();

        Plan::factory()->count(2)->create();
        $trashed = Plan::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/plans')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Plans/Index')
                ->has('plans')
            );

        $this->assertSoftDeleted('plans', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted plan', function () {
        $superAdmin = $this->superAdminUser();

        $plan = Plan::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/plans/{$plan->id}")
            ->assertStatus(404);
    });
});

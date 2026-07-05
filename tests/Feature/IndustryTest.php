<?php

use App\Models\Industry;
use App\Models\Log;
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

test('example', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

describe('index', function () {
    test('authenticated user with permission can list industries', function () {
        $superAdmin = $this->superAdminUser();

        Industry::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/industries')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Industries/Index')
                ->has('industries')
            );
    });

    test('unauthenticated user cannot list industries', function () {
        $this->get('/industries')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list industries', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/industries')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/industries/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Industries/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/industries/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/industries/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create an industry', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'title' => 'Software Development',
            'code' => '62012',
            'description' => 'Business and domestic software development.',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/industries', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Software Development']);

        $this->assertDatabaseHas('industries', [
            'title' => 'Software Development',
            'code' => '62012',
        ]);
    });

    test('user without permission cannot create an industry', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/industries', [
                'title' => 'Software Development',
                'code' => '62012',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/industries', [
                'code' => '62012',
                'description' => 'No title here.',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when code already exists', function () {
        $superAdmin = $this->superAdminUser();

        Industry::factory()->create(['code' => '62012']);

        $this->actingAs($superAdmin)
            ->postJson('/industries', [
                'title' => 'Duplicate code industry',
                'code' => '62012',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/industries', [
                'title' => 'Minimal industry',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('industries', [
            'title' => 'Minimal industry',
            'code' => null,
            'description' => null,
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/industries', [
                'title' => 'Industry with meta',
                'meta' => ['sector' => 'technology', 'tags' => ['software']],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('industries', ['title' => 'Industry with meta']);
    });
});

describe('show', function () {
    test('authenticated user with permission can view an industry', function () {
        $superAdmin = $this->superAdminUser();

        $industry = Industry::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/industries/{$industry->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Industries/Show')
                ->has('industry')
            );
    });

    test('unauthenticated user cannot view an industry', function () {
        $industry = Industry::factory()->create();

        $this->get("/industries/{$industry->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view an industry', function () {
        $user = $this->normalUser();

        $industry = Industry::factory()->create();

        $this->actingAs($user)
            ->get("/industries/{$industry->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent industry', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/industries/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $industry = Industry::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/industries/{$industry->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Industries/Edit')
                ->has('industry')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $industry = Industry::factory()->create();

        $this->get("/industries/{$industry->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $industry = Industry::factory()->create();

        $this->actingAs($user)
            ->get("/industries/{$industry->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update an industry', function () {
        $superAdmin = $this->superAdminUser();

        $industry = Industry::factory()->create(['title' => 'Old title']);

        $this->actingAs($superAdmin)
            ->putJson("/industries/{$industry->id}", ['title' => 'New title'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'New title']);

        $this->assertDatabaseHas('industries', [
            'id' => $industry->id,
            'title' => 'New title',
        ]);
    });

    test('patch verb also updates an industry', function () {
        $superAdmin = $this->superAdminUser();

        $industry = Industry::factory()->create(['description' => 'Old description']);

        $this->actingAs($superAdmin)
            ->patchJson("/industries/{$industry->id}", ['description' => 'New description'])
            ->assertStatus(200)
            ->assertJsonFragment(['description' => 'New description']);
    });

    test('user without permission cannot update an industry', function () {
        $user = $this->normalUser();

        $industry = Industry::factory()->create();

        $this->actingAs($user)
            ->putJson("/industries/{$industry->id}", ['title' => 'New title'])
            ->assertStatus(403);
    });

    test('update fails validation when code already exists on another industry', function () {
        $superAdmin = $this->superAdminUser();

        Industry::factory()->create(['code' => '62012']);
        $industry = Industry::factory()->create(['code' => '62020']);

        $this->actingAs($superAdmin)
            ->putJson("/industries/{$industry->id}", ['code' => '62012'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    });

    test('update allows an industry to keep its own code', function () {
        $superAdmin = $this->superAdminUser();

        $industry = Industry::factory()->create(['code' => '62012']);

        $this->actingAs($superAdmin)
            ->putJson("/industries/{$industry->id}", [
                'code' => '62012',
                'title' => 'Renamed industry',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('industries', [
            'id' => $industry->id,
            'code' => '62012',
            'title' => 'Renamed industry',
        ]);
    });

    test('nullable fields can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();

        $industry = Industry::factory()->create([
            'code' => '62012',
            'description' => 'Some description.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/industries/{$industry->id}", [
                'code' => null,
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('industries', [
            'id' => $industry->id,
            'code' => null,
            'description' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $industry = Industry::factory()->create([
            'code' => '62012',
            'description' => 'Original description.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/industries/{$industry->id}", [
                'title' => 'Updated title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('industries', [
            'id' => $industry->id,
            'title' => 'Updated title',
            'code' => '62012',
            'description' => 'Original description.',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();

        $industry = Industry::factory()->create([
            'description' => 'Some description.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/industries/{$industry->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('industries', [
            'id' => $industry->id,
            'description' => null,
        ]);
    });

    test('logs industry updates with actor id', function () {
        $actor = $this->adminUser();

        $industry = Industry::factory()->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/industries/{$industry->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_INDUSTRY)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete an industry', function () {
        $superAdmin = $this->superAdminUser();

        $industry = Industry::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/industries/{$industry->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('industries', ['id' => $industry->id]);
    });

    test('user without permission cannot soft delete an industry', function () {
        $user = $this->normalUser();

        $industry = Industry::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/industries/{$industry->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent industry', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/industries/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted industry', function () {
        $superAdmin = $this->superAdminUser();

        $industry = Industry::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/industries/{$industry->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('industries', [
            'id' => $industry->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore an industry', function () {
        $user = $this->normalUser();

        $industry = Industry::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/industries/{$industry->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for an industry that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $industry = Industry::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/industries/{$industry->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete an industry', function () {
        $superAdmin = $this->superAdminUser();

        $industry = Industry::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/industries/{$industry->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('industries', ['id' => $industry->id]);
    });

    test('user without permission cannot force delete an industry', function () {
        $user = $this->normalUser();

        $industry = Industry::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/industries/{$industry->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for an industry that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $industry = Industry::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/industries/{$industry->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete industries', function () {
        $superAdmin = $this->superAdminUser();

        $industries = Industry::factory()->count(3)->create();
        $ids = $industries->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/industries/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('industries', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/industries/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/industries/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete industries', function () {
        $user = $this->normalUser();

        $industries = Industry::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/industries/bulk/delete', [
                'ids' => $industries->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore industries', function () {
        $superAdmin = $this->superAdminUser();

        $industries = Industry::factory()->count(3)->deleted()->create();
        $ids = $industries->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/industries/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('industries', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/industries/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/industries/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore industries', function () {
        $user = $this->normalUser();

        $industries = Industry::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/industries/bulk/restore', [
                'ids' => $industries->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted industries', function () {
        $superAdmin = $this->superAdminUser();

        Industry::factory()->count(2)->create();
        $trashed = Industry::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/industries')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Industries/Index')
                ->has('industries')
            );

        $this->assertSoftDeleted('industries', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted industry', function () {
        $superAdmin = $this->superAdminUser();

        $industry = Industry::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/industries/{$industry->id}")
            ->assertStatus(404);
    });
});

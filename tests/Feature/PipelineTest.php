<?php

use App\Models\Log;
use App\Models\Pipeline;
use App\Models\PipelineStatus;
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
    test('authenticated user with permission can list pipelines', function () {
        $superAdmin = $this->superAdminUser();

        Pipeline::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/pipelines')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Pipelines/Index')
                ->has('pipelines')
            );
    });

    test('unauthenticated user cannot list pipelines', function () {
        $this->get('/pipelines')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list pipelines', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/pipelines')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/pipelines/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Pipelines/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/pipelines/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/pipelines/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $status = PipelineStatus::factory()->create();

        $payload = [
            'title' => 'Sales Pipeline',
            'description' => 'Tracks prospective deals through to close.',
            'is_default' => true,
            'status_id' => $status->id,
        ];

        $this->actingAs($superAdmin)
            ->postJson('/pipelines', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Sales Pipeline']);

        $this->assertDatabaseHas('pipelines', [
            'title' => 'Sales Pipeline',
            'is_default' => true,
            'status_id' => $status->id,
        ]);
    });

    test('user without permission cannot create a pipeline', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/pipelines', [
                'title' => 'Blocked Pipeline',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipelines', [
                'description' => 'Missing title',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when status_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipelines', [
                'title' => 'Invalid status pipeline',
                'status_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status_id']);
    });

    test('store fails validation when is_default is not a boolean', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipelines', [
                'title' => 'Invalid default flag pipeline',
                'is_default' => 'not-a-boolean',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_default']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipelines', [
                'title' => 'Minimal Pipeline',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('pipelines', [
            'title' => 'Minimal Pipeline',
            'description' => null,
            'status_id' => null,
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipelines', [
                'title' => 'Pipeline with meta',
                'meta' => ['colour_theme' => 'blue', 'tags' => ['priority']],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('pipelines', ['title' => 'Pipeline with meta']);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/pipelines/{$pipeline->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Pipelines/Show')
                ->has('pipeline')
            );
    });

    test('unauthenticated user cannot view a pipeline', function () {
        $pipeline = Pipeline::factory()->create();

        $this->get("/pipelines/{$pipeline->id}")
            ->assertRedirect('/login');
    });

    test('user cannot view a pipeline created by a super admin', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $pipeline = Pipeline::factory()->create(['created_by' => $superAdmin->id]);

        $this->actingAs($user)
            ->get("/pipelines/{$pipeline->id}")
            ->assertStatus(403);
    });

    test('user with permission can view a pipeline not created by a super admin', function () {
        $user = $this->normalUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($user)
            ->get("/pipelines/{$pipeline->id}")
            ->assertStatus(200);
    });

    test('user with no permissions at all cannot view a pipeline', function () {
        $user = $this->userWithNoPermissions();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($user)
            ->get("/pipelines/{$pipeline->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent pipeline', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/pipelines/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/pipelines/{$pipeline->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Pipelines/Edit')
                ->has('pipeline')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $pipeline = Pipeline::factory()->create();

        $this->get("/pipelines/{$pipeline->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($user)
            ->get("/pipelines/{$pipeline->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create(['title' => 'Old title']);

        $this->actingAs($superAdmin)
            ->putJson("/pipelines/{$pipeline->id}", ['title' => 'New title'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'New title']);

        $this->assertDatabaseHas('pipelines', [
            'id' => $pipeline->id,
            'title' => 'New title',
        ]);
    });

    test('patch verb also updates a pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create(['description' => 'Old description']);

        $this->actingAs($superAdmin)
            ->patchJson("/pipelines/{$pipeline->id}", ['description' => 'New description'])
            ->assertStatus(200)
            ->assertJsonFragment(['description' => 'New description']);
    });

    test('user without permission cannot update a pipeline', function () {
        $user = $this->normalUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($user)
            ->putJson("/pipelines/{$pipeline->id}", ['title' => 'New title'])
            ->assertStatus(403);
    });

    test('update fails validation when status_id does not exist', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/pipelines/{$pipeline->id}", ['status_id' => 99999])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status_id']);
    });

    test('update fails validation when is_default is not a boolean', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/pipelines/{$pipeline->id}", ['is_default' => 'not-a-boolean'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_default']);
    });

    test('nullable fields can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();
        $status = PipelineStatus::factory()->create();

        $pipeline = Pipeline::factory()->create([
            'description' => 'Some description.',
            'status_id' => $status->id,
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/pipelines/{$pipeline->id}", [
                'description' => null,
                'status_id' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('pipelines', [
            'id' => $pipeline->id,
            'description' => null,
            'status_id' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $pipeline = Pipeline::factory()->create([
            'description' => 'Original description.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/pipelines/{$pipeline->id}", [
                'title' => 'Updated title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('pipelines', [
            'id' => $pipeline->id,
            'title' => 'Updated title',
            'description' => 'Original description.',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();

        $pipeline = Pipeline::factory()->create([
            'description' => 'Some description.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/pipelines/{$pipeline->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('pipelines', [
            'id' => $pipeline->id,
            'description' => null,
        ]);
    });

    test('is_default flag can be updated', function () {
        $superAdmin = $this->superAdminUser();

        $pipeline = Pipeline::factory()->create(['is_default' => false]);

        $this->actingAs($superAdmin)
            ->putJson("/pipelines/{$pipeline->id}", ['is_default' => true])
            ->assertStatus(200);

        $this->assertDatabaseHas('pipelines', [
            'id' => $pipeline->id,
            'is_default' => true,
        ]);
    });

    test('status can be updated', function () {
        $superAdmin = $this->superAdminUser();
        $newStatus = PipelineStatus::factory()->create();

        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/pipelines/{$pipeline->id}", ['status_id' => $newStatus->id])
            ->assertStatus(200);

        $this->assertDatabaseHas('pipelines', [
            'id' => $pipeline->id,
            'status_id' => $newStatus->id,
        ]);
    });

    test('logs pipeline updates with actor id', function () {
        $actor = $this->adminUser();

        $pipeline = Pipeline::factory()->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/pipelines/{$pipeline->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_PIPELINE)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/pipelines/{$pipeline->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('pipelines', ['id' => $pipeline->id]);
    });

    test('user without permission cannot soft delete a pipeline', function () {
        $user = $this->normalUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/pipelines/{$pipeline->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent pipeline', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/pipelines/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('pipelines', [
            'id' => $pipeline->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a pipeline', function () {
        $user = $this->normalUser();
        $pipeline = Pipeline::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/pipelines/{$pipeline->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a pipeline that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/pipelines/{$pipeline->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('pipelines', ['id' => $pipeline->id]);
    });

    test('user without permission cannot force delete a pipeline', function () {
        $user = $this->normalUser();
        $pipeline = Pipeline::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/pipelines/{$pipeline->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a pipeline that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/pipelines/{$pipeline->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete pipelines', function () {
        $superAdmin = $this->superAdminUser();

        $pipelines = Pipeline::factory()->count(3)->create();
        $ids = $pipelines->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/pipelines/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('pipelines', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipelines/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipelines/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete pipelines', function () {
        $user = $this->normalUser();

        $pipelines = Pipeline::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/pipelines/bulk/delete', [
                'ids' => $pipelines->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore pipelines', function () {
        $superAdmin = $this->superAdminUser();

        $pipelines = Pipeline::factory()->count(3)->deleted()->create();
        $ids = $pipelines->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/pipelines/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('pipelines', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipelines/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipelines/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore pipelines', function () {
        $user = $this->normalUser();

        $pipelines = Pipeline::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/pipelines/bulk/restore', [
                'ids' => $pipelines->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted pipelines', function () {
        $superAdmin = $this->superAdminUser();

        Pipeline::factory()->count(2)->create();
        $trashed = Pipeline::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/pipelines')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Pipelines/Index')
                ->has('pipelines')
            );

        $this->assertSoftDeleted('pipelines', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/pipelines/{$pipeline->id}")
            ->assertStatus(404);
    });
});

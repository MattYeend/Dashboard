<?php

use App\Models\Log;
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

describe('index', function () {
    test('authenticated user with permission can list pipeline statuses', function () {
        $superAdmin = $this->superAdminUser();

        PipelineStatus::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/pipeline-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PipelineStatuses/Index')
                ->has('pipelineStatuses')
            );
    });

    test('unauthenticated user cannot list pipeline statuses', function () {
        $this->get('/pipeline-statuses')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list pipeline statuses', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/pipeline-statuses')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/pipeline-statuses/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PipelineStatuses/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/pipeline-statuses/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/pipeline-statuses/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a pipeline status', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'title' => 'Qualified',
            'description' => 'Lead has been qualified as a genuine opportunity.',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/pipeline-statuses', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Qualified']);

        $this->assertDatabaseHas('pipeline_statuses', [
            'title' => 'Qualified',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('user without permission cannot create a pipeline status', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/pipeline-statuses', [
                'title' => 'Qualified',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipeline-statuses', [
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipeline-statuses', [
                'title' => 'Qualified',
                'background_colour' => 'not-a-colour',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('store fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipeline-statuses', [
                'title' => 'Qualified',
                'background_colour' => '#bee3f8',
                'text_colour' => 'not-a-colour',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipeline-statuses', [
                'title' => 'New Lead',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('pipeline_statuses', [
            'title' => 'New Lead',
            'description' => null,
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipeline-statuses', [
                'title' => 'Negotiation',
                'meta' => ['order' => 3, 'icon' => 'handshake'],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('pipeline_statuses', [
            'title' => 'Negotiation',
        ]);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a pipeline status', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/pipeline-statuses/{$pipelineStatus->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PipelineStatuses/Show')
                ->has('pipelineStatus')
            );
    });

    test('unauthenticated user cannot view a pipeline status', function () {
        $pipelineStatus = PipelineStatus::factory()->create();

        $this->get("/pipeline-statuses/{$pipelineStatus->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a pipeline status', function () {
        $user = $this->userWithNoPermissions();

        $pipelineStatus = PipelineStatus::factory()->create();

        $this->actingAs($user)
            ->get("/pipeline-statuses/{$pipelineStatus->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent pipeline status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/pipeline-statuses/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/pipeline-statuses/{$pipelineStatus->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PipelineStatuses/Edit')
                ->has('pipelineStatus')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $pipelineStatus = PipelineStatus::factory()->create();

        $this->get("/pipeline-statuses/{$pipelineStatus->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $pipelineStatus = PipelineStatus::factory()->create();

        $this->actingAs($user)
            ->get("/pipeline-statuses/{$pipelineStatus->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a pipeline status', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->create(['title' => 'New Lead']);

        $this->actingAs($superAdmin)
            ->putJson("/pipeline-statuses/{$pipelineStatus->id}", ['title' => 'Qualified'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'Qualified']);

        $this->assertDatabaseHas('pipeline_statuses', [
            'id' => $pipelineStatus->id,
            'title' => 'Qualified',
        ]);
    });

    test('patch verb also updates a pipeline status', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->create(['background_colour' => '#ffffff']);

        $this->actingAs($superAdmin)
            ->patchJson("/pipeline-statuses/{$pipelineStatus->id}", ['background_colour' => '#bee3f8'])
            ->assertStatus(200)
            ->assertJsonFragment(['background_colour' => '#bee3f8']);
    });

    test('user without permission cannot update a pipeline status', function () {
        $user = $this->normalUser();

        $pipelineStatus = PipelineStatus::factory()->create();

        $this->actingAs($user)
            ->putJson("/pipeline-statuses/{$pipelineStatus->id}", ['title' => 'Qualified'])
            ->assertStatus(403);
    });

    test('update fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/pipeline-statuses/{$pipelineStatus->id}", ['background_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('update fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/pipeline-statuses/{$pipelineStatus->id}", ['text_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('description can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->create([
            'description' => 'Lead has been qualified as a genuine opportunity.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/pipeline-statuses/{$pipelineStatus->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('pipeline_statuses', [
            'id' => $pipelineStatus->id,
            'description' => null,
        ]);
    });

    test('background_colour and text_colour cannot be nulled and fail validation', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->create([
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/pipeline-statuses/{$pipelineStatus->id}", [
                'background_colour' => null,
                'text_colour' => null,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour', 'text_colour']);

        $this->assertDatabaseHas('pipeline_statuses', [
            'id' => $pipelineStatus->id,
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->create([
            'description' => 'Original description.',
            'background_colour' => '#bee3f8',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/pipeline-statuses/{$pipelineStatus->id}", [
                'title' => 'Updated Title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('pipeline_statuses', [
            'id' => $pipelineStatus->id,
            'title' => 'Updated Title',
            'description' => 'Original description.',
            'background_colour' => '#bee3f8',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->create([
            'description' => 'Some description.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/pipeline-statuses/{$pipelineStatus->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('pipeline_statuses', [
            'id' => $pipelineStatus->id,
            'description' => null,
        ]);
    });

    test('logs pipeline status updates with actor id', function () {
        $actor = $this->adminUser();

        $pipelineStatus = PipelineStatus::factory()->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/pipeline-statuses/{$pipelineStatus->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_PIPELINE_STATUS)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a pipeline status', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/pipeline-statuses/{$pipelineStatus->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('pipeline_statuses', ['id' => $pipelineStatus->id]);
    });

    test('user without permission cannot soft delete a pipeline status', function () {
        $user = $this->normalUser();

        $pipelineStatus = PipelineStatus::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/pipeline-statuses/{$pipelineStatus->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent pipeline status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/pipeline-statuses/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted pipeline status', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/pipeline-statuses/{$pipelineStatus->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('pipeline_statuses', [
            'id' => $pipelineStatus->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a pipeline status', function () {
        $user = $this->normalUser();

        $pipelineStatus = PipelineStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/pipeline-statuses/{$pipelineStatus->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a pipeline status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/pipeline-statuses/{$pipelineStatus->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a pipeline status', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/pipeline-statuses/{$pipelineStatus->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('pipeline_statuses', ['id' => $pipelineStatus->id]);
    });

    test('user without permission cannot force delete a pipeline status', function () {
        $user = $this->normalUser();

        $pipelineStatus = PipelineStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/pipeline-statuses/{$pipelineStatus->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a pipeline status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/pipeline-statuses/{$pipelineStatus->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete pipeline statuses', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatuses = PipelineStatus::factory()->count(3)->create();
        $ids = $pipelineStatuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/pipeline-statuses/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('pipeline_statuses', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipeline-statuses/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipeline-statuses/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete pipeline statuses', function () {
        $user = $this->normalUser();

        $pipelineStatuses = PipelineStatus::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/pipeline-statuses/bulk/delete', [
                'ids' => $pipelineStatuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore pipeline statuses', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatuses = PipelineStatus::factory()->count(3)->deleted()->create();
        $ids = $pipelineStatuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/pipeline-statuses/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('pipeline_statuses', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipeline-statuses/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/pipeline-statuses/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore pipeline statuses', function () {
        $user = $this->normalUser();

        $pipelineStatuses = PipelineStatus::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/pipeline-statuses/bulk/restore', [
                'ids' => $pipelineStatuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted pipeline statuses', function () {
        $superAdmin = $this->superAdminUser();

        PipelineStatus::factory()->count(2)->create();
        $trashed = PipelineStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/pipeline-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PipelineStatuses/Index')
                ->has('pipelineStatuses')
            );

        $this->assertSoftDeleted('pipeline_statuses', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted pipeline status', function () {
        $superAdmin = $this->superAdminUser();

        $pipelineStatus = PipelineStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/pipeline-statuses/{$pipelineStatus->id}")
            ->assertStatus(404);
    });
});

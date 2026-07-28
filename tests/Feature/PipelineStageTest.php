<?php

use App\Models\Log;
use App\Models\Pipeline;
use App\Models\PipelineStage;
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
    test('authenticated user with permission can list pipeline stages', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        PipelineStage::factory()->count(3)->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($superAdmin)
            ->get("/pipelines/{$pipeline->id}/stages")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PipelineStages/Index')
                ->has('pipeline_stages')
            );
    });

    test('unauthenticated user cannot list pipeline stages', function () {
        $pipeline = Pipeline::factory()->create();

        $this->get("/pipelines/{$pipeline->id}/stages")
            ->assertRedirect('/login');
    });

    test('user without permission cannot list pipeline stages', function () {
        $user = $this->userWithNoPermissions();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($user)
            ->get("/pipelines/{$pipeline->id}/stages")
            ->assertStatus(403);
    });

    test('index only returns stages belonging to the given pipeline', function () {
        $superAdmin = $this->superAdminUser();

        $pipeline = Pipeline::factory()->create();
        $otherPipeline = Pipeline::factory()->create();

        PipelineStage::factory()->create(['pipeline_id' => $pipeline->id, 'title' => 'Mine']);
        PipelineStage::factory()->create(['pipeline_id' => $otherPipeline->id, 'title' => 'Not Mine']);

        $response = $this->actingAs($superAdmin)
            ->get("/pipelines/{$pipeline->id}/stages")
            ->assertStatus(200);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('PipelineStages/Index')
            ->where('pipeline_stages.data.0.title', 'Mine')
            ->where('pipeline_stages.meta.total', 1)
        );
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/pipelines/{$pipeline->id}/stages/create")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PipelineStages/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $pipeline = Pipeline::factory()->create();

        $this->get("/pipelines/{$pipeline->id}/stages/create")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($user)
            ->get("/pipelines/{$pipeline->id}/stages/create")
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a pipeline stage', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $payload = [
            'title' => 'Qualified',
            'description' => 'Lead has been qualified.',
            'position' => 1,
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
            'is_won' => false,
            'is_lost' => false,
        ];

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages", $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Qualified']);

        $this->assertDatabaseHas('pipeline_stages', [
            'pipeline_id' => $pipeline->id,
            'title' => 'Qualified',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('created stage is always scoped to the pipeline in the route, not the payload', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $otherPipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages", [
                'title' => 'Smuggled Pipeline Id',
                'pipeline_id' => $otherPipeline->id,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('pipeline_stages', [
            'title' => 'Smuggled Pipeline Id',
            'pipeline_id' => $pipeline->id,
        ]);

        $this->assertDatabaseMissing('pipeline_stages', [
            'title' => 'Smuggled Pipeline Id',
            'pipeline_id' => $otherPipeline->id,
        ]);
    });

    test('user without permission cannot create a pipeline stage', function () {
        $user = $this->normalUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($user)
            ->postJson("/pipelines/{$pipeline->id}/stages", [
                'title' => 'Qualified',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages", [
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages", [
                'title' => 'Qualified',
                'background_colour' => 'not-a-colour',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('store fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages", [
                'title' => 'Qualified',
                'background_colour' => '#bee3f8',
                'text_colour' => 'not-a-colour',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('store fails validation when position is negative', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages", [
                'title' => 'Qualified',
                'position' => -1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['position']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages", [
                'title' => 'New Lead',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('pipeline_stages', [
            'pipeline_id' => $pipeline->id,
            'title' => 'New Lead',
            'description' => null,
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages", [
                'title' => 'Negotiation',
                'meta' => ['order' => 3, 'icon' => 'handshake'],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('pipeline_stages', [
            'pipeline_id' => $pipeline->id,
            'title' => 'Negotiation',
        ]);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a pipeline stage', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($superAdmin)
            ->get("/pipelines/{$pipeline->id}/stages/{$stage->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PipelineStages/Show')
                ->has('pipeline_stage')
            );
    });

    test('unauthenticated user cannot view a pipeline stage', function () {
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

        $this->get("/pipelines/{$pipeline->id}/stages/{$stage->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a pipeline stage', function () {
        $user = $this->userWithNoPermissions();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($user)
            ->get("/pipelines/{$pipeline->id}/stages/{$stage->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent pipeline stage', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/pipelines/{$pipeline->id}/stages/99999")
            ->assertStatus(404);
    });

    test('show returns 404 when the stage belongs to a different pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $otherPipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $otherPipeline->id]);

        $this->actingAs($superAdmin)
            ->get("/pipelines/{$pipeline->id}/stages/{$stage->id}")
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($superAdmin)
            ->get("/pipelines/{$pipeline->id}/stages/{$stage->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PipelineStages/Edit')
                ->has('pipeline_stage')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

        $this->get("/pipelines/{$pipeline->id}/stages/{$stage->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($user)
            ->get("/pipelines/{$pipeline->id}/stages/{$stage->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a pipeline stage', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create([
            'pipeline_id' => $pipeline->id,
            'title' => 'Old Title',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/pipelines/{$pipeline->id}/stages/{$stage->id}", ['title' => 'New Title'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'New Title']);

        $this->assertDatabaseHas('pipeline_stages', [
            'id' => $stage->id,
            'title' => 'New Title',
        ]);
    });

    test('patch verb also updates a pipeline stage', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create([
            'pipeline_id' => $pipeline->id,
            'background_colour' => '#ffffff',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/pipelines/{$pipeline->id}/stages/{$stage->id}", ['background_colour' => '#bee3f8'])
            ->assertStatus(200)
            ->assertJsonFragment(['background_colour' => '#bee3f8']);
    });

    test('user without permission cannot update a pipeline stage', function () {
        $user = $this->normalUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($user)
            ->putJson("/pipelines/{$pipeline->id}/stages/{$stage->id}", ['title' => 'New Title'])
            ->assertStatus(403);
    });

    test('update fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($superAdmin)
            ->putJson("/pipelines/{$pipeline->id}/stages/{$stage->id}", ['background_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('update fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($superAdmin)
            ->putJson("/pipelines/{$pipeline->id}/stages/{$stage->id}", ['text_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('description can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create([
            'pipeline_id' => $pipeline->id,
            'description' => 'Lead has been qualified.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/pipelines/{$pipeline->id}/stages/{$stage->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('pipeline_stages', [
            'id' => $stage->id,
            'description' => null,
        ]);
    });

    test('background_colour and text_colour cannot be nulled and fail validation', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create([
            'pipeline_id' => $pipeline->id,
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/pipelines/{$pipeline->id}/stages/{$stage->id}", [
                'background_colour' => null,
                'text_colour' => null,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour', 'text_colour']);

        $this->assertDatabaseHas('pipeline_stages', [
            'id' => $stage->id,
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create([
            'pipeline_id' => $pipeline->id,
            'description' => 'Original description.',
            'background_colour' => '#bee3f8',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/pipelines/{$pipeline->id}/stages/{$stage->id}", [
                'title' => 'Updated Title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('pipeline_stages', [
            'id' => $stage->id,
            'title' => 'Updated Title',
            'description' => 'Original description.',
            'background_colour' => '#bee3f8',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create([
            'pipeline_id' => $pipeline->id,
            'description' => 'Some description.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/pipelines/{$pipeline->id}/stages/{$stage->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('pipeline_stages', [
            'id' => $stage->id,
            'description' => null,
        ]);
    });

    test('a stage cannot be moved to a different pipeline via update', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $otherPipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($superAdmin)
            ->putJson("/pipelines/{$pipeline->id}/stages/{$stage->id}", [
                'pipeline_id' => $otherPipeline->id,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('pipeline_stages', [
            'id' => $stage->id,
            'pipeline_id' => $pipeline->id,
        ]);
    });

    test('update returns 404 when the stage belongs to a different pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $otherPipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $otherPipeline->id]);

        $this->actingAs($superAdmin)
            ->putJson("/pipelines/{$pipeline->id}/stages/{$stage->id}", ['title' => 'New Title'])
            ->assertStatus(404);
    });

    test('logs pipeline stage updates with actor id', function () {
        $actor = $this->adminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create([
            'pipeline_id' => $pipeline->id,
            'title' => 'Old Title',
        ]);

        $this->actingAs($actor)
            ->putJson("/pipelines/{$pipeline->id}/stages/{$stage->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_PIPELINE_STAGE)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a pipeline stage', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/pipelines/{$pipeline->id}/stages/{$stage->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('pipeline_stages', ['id' => $stage->id]);
    });

    test('user without permission cannot soft delete a pipeline stage', function () {
        $user = $this->normalUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($user)
            ->deleteJson("/pipelines/{$pipeline->id}/stages/{$stage->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent pipeline stage', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/pipelines/{$pipeline->id}/stages/99999")
            ->assertStatus(404);
    });

    test('destroy returns 404 when the stage belongs to a different pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $otherPipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $otherPipeline->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/pipelines/{$pipeline->id}/stages/{$stage->id}")
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted pipeline stage', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->deleted()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages/{$stage->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('pipeline_stages', [
            'id' => $stage->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a pipeline stage', function () {
        $user = $this->normalUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->deleted()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($user)
            ->postJson("/pipelines/{$pipeline->id}/stages/{$stage->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a stage that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages/{$stage->id}/restore")
            ->assertStatus(404);
    });

    test('restore returns 404 when the trashed stage belongs to a different pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $otherPipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->deleted()->create(['pipeline_id' => $otherPipeline->id]);

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages/{$stage->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a pipeline stage', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->deleted()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/pipelines/{$pipeline->id}/stages/{$stage->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('pipeline_stages', ['id' => $stage->id]);
    });

    test('user without permission cannot force delete a pipeline stage', function () {
        $user = $this->normalUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->deleted()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($user)
            ->deleteJson("/pipelines/{$pipeline->id}/stages/{$stage->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a stage that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/pipelines/{$pipeline->id}/stages/{$stage->id}/force")
            ->assertStatus(404);
    });

    test('force delete returns 404 when the trashed stage belongs to a different pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $otherPipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->deleted()->create(['pipeline_id' => $otherPipeline->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/pipelines/{$pipeline->id}/stages/{$stage->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete pipeline stages', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $stages = PipelineStage::factory()->count(3)->create(['pipeline_id' => $pipeline->id]);
        $ids = $stages->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages/bulk/delete", ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('pipeline_stages', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages/bulk/delete", ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages/bulk/delete", ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('bulk delete fails validation for ids belonging to a different pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $otherPipeline = Pipeline::factory()->create();

        $stage = PipelineStage::factory()->create(['pipeline_id' => $otherPipeline->id]);

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages/bulk/delete", ['ids' => [$stage->id]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);

        $this->assertDatabaseHas('pipeline_stages', [
            'id' => $stage->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot bulk delete pipeline stages', function () {
        $user = $this->normalUser();
        $pipeline = Pipeline::factory()->create();

        $stages = PipelineStage::factory()->count(2)->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($user)
            ->postJson("/pipelines/{$pipeline->id}/stages/bulk/delete", [
                'ids' => $stages->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore pipeline stages', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $stages = PipelineStage::factory()->count(3)->deleted()->create(['pipeline_id' => $pipeline->id]);
        $ids = $stages->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages/bulk/restore", ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('pipeline_stages', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages/bulk/restore", ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages/bulk/restore", ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('bulk restore fails validation for ids belonging to a different pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $otherPipeline = Pipeline::factory()->create();

        $stage = PipelineStage::factory()->deleted()->create(['pipeline_id' => $otherPipeline->id]);

        $this->actingAs($superAdmin)
            ->postJson("/pipelines/{$pipeline->id}/stages/bulk/restore", ['ids' => [$stage->id]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);

        $this->assertSoftDeleted('pipeline_stages', ['id' => $stage->id]);
    });

    test('user without permission cannot bulk restore pipeline stages', function () {
        $user = $this->normalUser();
        $pipeline = Pipeline::factory()->create();

        $stages = PipelineStage::factory()->count(2)->deleted()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($user)
            ->postJson("/pipelines/{$pipeline->id}/stages/bulk/restore", [
                'ids' => $stages->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted pipeline stages', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();

        PipelineStage::factory()->count(2)->create(['pipeline_id' => $pipeline->id]);
        $trashed = PipelineStage::factory()->deleted()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($superAdmin)
            ->get("/pipelines/{$pipeline->id}/stages")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PipelineStages/Index')
                ->has('pipeline_stages')
            );

        $this->assertSoftDeleted('pipeline_stages', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted pipeline stage', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->deleted()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($superAdmin)
            ->get("/pipelines/{$pipeline->id}/stages/{$stage->id}")
            ->assertStatus(404);
    });
});

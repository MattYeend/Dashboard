<?php

use App\Models\DealStatus;
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

describe('index', function () {
    test('authenticated user with permission can list deal statuses', function () {
        $superAdmin = $this->superAdminUser();

        DealStatus::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/deal-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('DealStatuses/Index')
                ->has('dealStatuses')
            );
    });

    test('unauthenticated user cannot list deal statuses', function () {
        $this->get('/deal-statuses')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list deal statuses', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/deal-statuses')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/deal-statuses/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('DealStatuses/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/deal-statuses/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/deal-statuses/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a deal status', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'title' => 'Qualified',
            'description' => 'Deal has been reviewed and meets the criteria to be pursued.',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/deal-statuses', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Qualified']);

        $this->assertDatabaseHas('deal_statuses', [
            'title' => 'Qualified',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('user without permission cannot create a deal status', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/deal-statuses', [
                'title' => 'Qualified',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deal-statuses', [
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deal-statuses', [
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
            ->postJson('/deal-statuses', [
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
            ->postJson('/deal-statuses', [
                'title' => 'New',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('deal_statuses', [
            'title' => 'New',
            'description' => null,
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deal-statuses', [
                'title' => 'Negotiation',
                'meta' => ['order' => 3, 'icon' => 'handshake'],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('deal_statuses', [
            'title' => 'Negotiation',
        ]);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a deal status', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/deal-statuses/{$dealStatus->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('DealStatuses/Show')
                ->has('dealStatus')
            );
    });

    test('unauthenticated user cannot view a deal status', function () {
        $dealStatus = DealStatus::factory()->create();

        $this->get("/deal-statuses/{$dealStatus->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a deal status', function () {
        $user = $this->userWithNoPermissions();

        $dealStatus = DealStatus::factory()->create();

        $this->actingAs($user)
            ->get("/deal-statuses/{$dealStatus->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent deal status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/deal-statuses/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/deal-statuses/{$dealStatus->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('DealStatuses/Edit')
                ->has('dealStatus')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $dealStatus = DealStatus::factory()->create();

        $this->get("/deal-statuses/{$dealStatus->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $dealStatus = DealStatus::factory()->create();

        $this->actingAs($user)
            ->get("/deal-statuses/{$dealStatus->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a deal status', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->create(['title' => 'New']);

        $this->actingAs($superAdmin)
            ->putJson("/deal-statuses/{$dealStatus->id}", ['title' => 'Qualified'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'Qualified']);

        $this->assertDatabaseHas('deal_statuses', [
            'id' => $dealStatus->id,
            'title' => 'Qualified',
        ]);
    });

    test('patch verb also updates a deal status', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->create(['background_colour' => '#ffffff']);

        $this->actingAs($superAdmin)
            ->patchJson("/deal-statuses/{$dealStatus->id}", ['background_colour' => '#bee3f8'])
            ->assertStatus(200)
            ->assertJsonFragment(['background_colour' => '#bee3f8']);
    });

    test('user without permission cannot update a deal status', function () {
        $user = $this->normalUser();

        $dealStatus = DealStatus::factory()->create();

        $this->actingAs($user)
            ->putJson("/deal-statuses/{$dealStatus->id}", ['title' => 'Qualified'])
            ->assertStatus(403);
    });

    test('update fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/deal-statuses/{$dealStatus->id}", ['background_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('update fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/deal-statuses/{$dealStatus->id}", ['text_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('description can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->create([
            'description' => 'Deal has been reviewed and meets the criteria to be pursued.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/deal-statuses/{$dealStatus->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('deal_statuses', [
            'id' => $dealStatus->id,
            'description' => null,
        ]);
    });

    test('background_colour and text_colour cannot be nulled and fail validation', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->create([
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/deal-statuses/{$dealStatus->id}", [
                'background_colour' => null,
                'text_colour' => null,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour', 'text_colour']);

        $this->assertDatabaseHas('deal_statuses', [
            'id' => $dealStatus->id,
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->create([
            'description' => 'Original description.',
            'background_colour' => '#bee3f8',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/deal-statuses/{$dealStatus->id}", [
                'title' => 'Updated Title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('deal_statuses', [
            'id' => $dealStatus->id,
            'title' => 'Updated Title',
            'description' => 'Original description.',
            'background_colour' => '#bee3f8',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->create([
            'description' => 'Some description.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/deal-statuses/{$dealStatus->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('deal_statuses', [
            'id' => $dealStatus->id,
            'description' => null,
        ]);
    });

    test('logs deal status updates with actor id', function () {
        $actor = $this->adminUser();

        $dealStatus = DealStatus::factory()->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/deal-statuses/{$dealStatus->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_DEAL_STATUS)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a deal status', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/deal-statuses/{$dealStatus->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('deal_statuses', ['id' => $dealStatus->id]);
    });

    test('user without permission cannot soft delete a deal status', function () {
        $user = $this->normalUser();

        $dealStatus = DealStatus::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/deal-statuses/{$dealStatus->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent deal status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/deal-statuses/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted deal status', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/deal-statuses/{$dealStatus->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('deal_statuses', [
            'id' => $dealStatus->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a deal status', function () {
        $user = $this->normalUser();

        $dealStatus = DealStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/deal-statuses/{$dealStatus->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a deal status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/deal-statuses/{$dealStatus->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a deal status', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/deal-statuses/{$dealStatus->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('deal_statuses', ['id' => $dealStatus->id]);
    });

    test('user without permission cannot force delete a deal status', function () {
        $user = $this->normalUser();

        $dealStatus = DealStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/deal-statuses/{$dealStatus->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a deal status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/deal-statuses/{$dealStatus->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete deal statuses', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatuses = DealStatus::factory()->count(3)->create();
        $ids = $dealStatuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/deal-statuses/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('deal_statuses', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deal-statuses/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deal-statuses/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete deal statuses', function () {
        $user = $this->normalUser();

        $dealStatuses = DealStatus::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/deal-statuses/bulk/delete', [
                'ids' => $dealStatuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore deal statuses', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatuses = DealStatus::factory()->count(3)->deleted()->create();
        $ids = $dealStatuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/deal-statuses/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('deal_statuses', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deal-statuses/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deal-statuses/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore deal statuses', function () {
        $user = $this->normalUser();

        $dealStatuses = DealStatus::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/deal-statuses/bulk/restore', [
                'ids' => $dealStatuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted deal statuses', function () {
        $superAdmin = $this->superAdminUser();

        DealStatus::factory()->count(2)->create();
        $trashed = DealStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/deal-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('DealStatuses/Index')
                ->has('dealStatuses')
            );

        $this->assertSoftDeleted('deal_statuses', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted deal status', function () {
        $superAdmin = $this->superAdminUser();

        $dealStatus = DealStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/deal-statuses/{$dealStatus->id}")
            ->assertStatus(404);
    });
});

<?php

declare(strict_types=1);

use App\Models\CampaignStatus;
use App\Models\Log;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

describe('index', function () {
    test('authenticated user with permission can list campaign statuses', function () {
        $superAdmin = $this->superAdminUser();

        CampaignStatus::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/campaign-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('CampaignStatuses/Index')
                ->has('campaignStatuses')
            );
    });

    test('unauthenticated user cannot list campaign statuses', function () {
        $this->get('/campaign-statuses')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list campaign statuses', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/campaign-statuses')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/campaign-statuses/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('CampaignStatuses/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/campaign-statuses/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/campaign-statuses/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a campaign status', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'title' => 'Qualified',
            'description' => 'Campaign has been reviewed and meets the criteria to be pursued.',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/campaign-statuses', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Qualified']);

        $this->assertDatabaseHas('campaign_statuses', [
            'title' => 'Qualified',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('user without permission cannot create a campaign status', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/campaign-statuses', [
                'title' => 'Qualified',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaign-statuses', [
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaign-statuses', [
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
            ->postJson('/campaign-statuses', [
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
            ->postJson('/campaign-statuses', [
                'title' => 'New',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('campaign_statuses', [
            'title' => 'New',
            'description' => null,
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaign-statuses', [
                'title' => 'Negotiation',
                'meta' => ['order' => 3, 'icon' => 'handshake'],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('campaign_statuses', [
            'title' => 'Negotiation',
        ]);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a campaign status', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/campaign-statuses/{$campaignStatus->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('CampaignStatuses/Show')
                ->has('campaignStatus')
            );
    });

    test('unauthenticated user cannot view a campaign status', function () {
        $campaignStatus = CampaignStatus::factory()->create();

        $this->get("/campaign-statuses/{$campaignStatus->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a campaign status', function () {
        $user = $this->userWithNoPermissions();

        $campaignStatus = CampaignStatus::factory()->create();

        $this->actingAs($user)
            ->get("/campaign-statuses/{$campaignStatus->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent campaign status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/campaign-statuses/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/campaign-statuses/{$campaignStatus->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('CampaignStatuses/Edit')
                ->has('campaignStatus')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $campaignStatus = CampaignStatus::factory()->create();

        $this->get("/campaign-statuses/{$campaignStatus->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $campaignStatus = CampaignStatus::factory()->create();

        $this->actingAs($user)
            ->get("/campaign-statuses/{$campaignStatus->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a campaign status', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->create(['title' => 'New']);

        $this->actingAs($superAdmin)
            ->putJson("/campaign-statuses/{$campaignStatus->id}", ['title' => 'Qualified'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'Qualified']);

        $this->assertDatabaseHas('campaign_statuses', [
            'id' => $campaignStatus->id,
            'title' => 'Qualified',
        ]);
    });

    test('patch verb also updates a campaign status', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->create(['background_colour' => '#ffffff']);

        $this->actingAs($superAdmin)
            ->patchJson("/campaign-statuses/{$campaignStatus->id}", ['background_colour' => '#bee3f8'])
            ->assertStatus(200)
            ->assertJsonFragment(['background_colour' => '#bee3f8']);
    });

    test('user without permission cannot update a campaign status', function () {
        $user = $this->normalUser();

        $campaignStatus = CampaignStatus::factory()->create();

        $this->actingAs($user)
            ->putJson("/campaign-statuses/{$campaignStatus->id}", ['title' => 'Qualified'])
            ->assertStatus(403);
    });

    test('update fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/campaign-statuses/{$campaignStatus->id}", ['background_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('update fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/campaign-statuses/{$campaignStatus->id}", ['text_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('description can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->create([
            'description' => 'Campaign has been reviewed and meets the criteria to be pursued.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/campaign-statuses/{$campaignStatus->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('campaign_statuses', [
            'id' => $campaignStatus->id,
            'description' => null,
        ]);
    });

    test('background_colour and text_colour cannot be nulled and fail validation', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->create([
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/campaign-statuses/{$campaignStatus->id}", [
                'background_colour' => null,
                'text_colour' => null,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour', 'text_colour']);

        $this->assertDatabaseHas('campaign_statuses', [
            'id' => $campaignStatus->id,
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->create([
            'description' => 'Original description.',
            'background_colour' => '#bee3f8',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/campaign-statuses/{$campaignStatus->id}", [
                'title' => 'Updated Title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('campaign_statuses', [
            'id' => $campaignStatus->id,
            'title' => 'Updated Title',
            'description' => 'Original description.',
            'background_colour' => '#bee3f8',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->create([
            'description' => 'Some description.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/campaign-statuses/{$campaignStatus->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('campaign_statuses', [
            'id' => $campaignStatus->id,
            'description' => null,
        ]);
    });

    test('logs campaign status updates with actor id', function () {
        $actor = $this->adminUser();

        $campaignStatus = CampaignStatus::factory()->create([
            'title' => 'Old Title',
            'created_by' => $actor->id,
        ]);

        $this->actingAs($actor)
            ->putJson("/campaign-statuses/{$campaignStatus->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_CAMPAIGN_STATUS)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a campaign status', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/campaign-statuses/{$campaignStatus->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('campaign_statuses', ['id' => $campaignStatus->id]);
    });

    test('user without permission cannot soft delete a campaign status', function () {
        $user = $this->normalUser();

        $campaignStatus = CampaignStatus::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/campaign-statuses/{$campaignStatus->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent campaign status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/campaign-statuses/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted campaign status', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/campaign-statuses/{$campaignStatus->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('campaign_statuses', [
            'id' => $campaignStatus->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a campaign status', function () {
        $user = $this->normalUser();

        $campaignStatus = CampaignStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/campaign-statuses/{$campaignStatus->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a campaign status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/campaign-statuses/{$campaignStatus->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a campaign status', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/campaign-statuses/{$campaignStatus->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('campaign_statuses', ['id' => $campaignStatus->id]);
    });

    test('user without permission cannot force delete a campaign status', function () {
        $user = $this->normalUser();

        $campaignStatus = CampaignStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/campaign-statuses/{$campaignStatus->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a campaign status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/campaign-statuses/{$campaignStatus->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete campaign statuses', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->count(3)->create();
        $ids = $campaignStatus->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/campaign-statuses/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('campaign_statuses', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaign-statuses/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaign-statuses/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete campaign statuses', function () {
        $user = $this->normalUser();

        $campaignStatus = CampaignStatus::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/campaign-statuses/bulk/delete', [
                'ids' => $campaignStatus->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore campaign statuses', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->count(3)->deleted()->create();
        $ids = $campaignStatus->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/campaign-statuses/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('campaign_statuses', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaign-statuses/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaign-statuses/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore campaign statuses', function () {
        $user = $this->normalUser();

        $campaignStatus = CampaignStatus::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/campaign-statuses/bulk/restore', [
                'ids' => $campaignStatus->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted campaign statuses', function () {
        $superAdmin = $this->superAdminUser();

        CampaignStatus::factory()->count(2)->create();
        $trashed = CampaignStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/campaign-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('CampaignStatuses/Index')
                ->has('campaignStatuses')
            );

        $this->assertSoftDeleted('campaign_statuses', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted campaign status', function () {
        $superAdmin = $this->superAdminUser();

        $campaignStatus = CampaignStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/campaign-statuses/{$campaignStatus->id}")
            ->assertStatus(404);
    });
});

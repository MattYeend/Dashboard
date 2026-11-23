<?php

declare(strict_types=1);

use App\Models\Campaign;
use App\Models\CampaignSegment;
use App\Models\EmailTemplate;
use App\Models\Log;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

if (! function_exists('campaignPayload')) {
    /**
     * Build a valid campaign payload, creating the required relations.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    function campaignPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Autumn Launch',
            'email_template_id' => EmailTemplate::factory()->create()->id,
            'campaign_segment_id' => CampaignSegment::factory()->create()->id,
        ], $overrides);
    }
}

describe('index', function () {
    test('authenticated user with permission can list campaigns', function () {
        $superAdmin = $this->superAdminUser();

        Campaign::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/campaigns')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Campaigns/Index')
                ->has('campaigns')
                ->has('permissions_meta')
            );
    });

    test('unauthenticated user cannot list campaigns', function () {
        $this->get('/campaigns')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list campaigns', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/campaigns')
            ->assertStatus(403);
    });

    test('index can be searched by name', function () {
        $superAdmin = $this->superAdminUser();

        Campaign::factory()->create(['name' => 'Spring Offer']);
        Campaign::factory()->create(['name' => 'Autumn Newsletter']);

        $this->actingAs($superAdmin)
            ->get('/campaigns?search=Spring')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Campaigns/Index')
                ->has('campaigns.data', 1)
            );
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/campaigns/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Campaigns/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/campaigns/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/campaigns/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a campaign', function () {
        $superAdmin = $this->superAdminUser();

        $payload = campaignPayload(['name' => 'Autumn Launch']);

        $this->actingAs($superAdmin)
            ->postJson('/campaigns', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Autumn Launch']);

        $this->assertDatabaseHas('campaigns', [
            'name' => 'Autumn Launch',
            'email_template_id' => $payload['email_template_id'],
            'campaign_segment_id' => $payload['campaign_segment_id'],
        ]);
    });

    test('store redirects to the show page for non-JSON requests', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->post('/campaigns', campaignPayload(['name' => 'Redirect Campaign']))
            ->assertRedirect();

        $campaign = Campaign::where('name', 'Redirect Campaign')->firstOrFail();

        expect($campaign->id)->toBeInt();
    });

    test('user without permission cannot create a campaign', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/campaigns', campaignPayload(['name' => 'Blocked Campaign']))
            ->assertStatus(403);
    });

    test('unauthenticated user cannot create a campaign', function () {
        $this->postJson('/campaigns', campaignPayload(['name' => 'Anonymous Campaign']))
            ->assertStatus(401);
    });

    test('store fails validation with an empty payload', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email_template_id', 'campaign_segment_id']);
    });

    test('store fails validation when name is missing', function () {
        $superAdmin = $this->superAdminUser();

        $payload = campaignPayload();
        unset($payload['name']);

        $this->actingAs($superAdmin)
            ->postJson('/campaigns', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('store fails validation when name exceeds max length', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns', campaignPayload(['name' => str_repeat('a', 256)]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('store fails validation when email_template_id is missing', function () {
        $superAdmin = $this->superAdminUser();

        $payload = campaignPayload();
        unset($payload['email_template_id']);

        $this->actingAs($superAdmin)
            ->postJson('/campaigns', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email_template_id']);
    });

    test('store fails validation when email_template_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns', campaignPayload(['email_template_id' => 99999]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email_template_id']);
    });

    test('store fails validation when campaign_segment_id is missing', function () {
        $superAdmin = $this->superAdminUser();

        $payload = campaignPayload();
        unset($payload['campaign_segment_id']);

        $this->actingAs($superAdmin)
            ->postJson('/campaigns', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['campaign_segment_id']);
    });

    test('store fails validation when campaign_segment_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns', campaignPayload(['campaign_segment_id' => 99999]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['campaign_segment_id']);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns', campaignPayload([
                'name' => 'Campaign with meta',
                'meta' => ['category' => 'marketing'],
            ]))
            ->assertStatus(201);

        $this->assertDatabaseHas('campaigns', ['name' => 'Campaign with meta']);
    });

    test('logs campaign creation with actor id', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns', campaignPayload(['name' => 'Logged Campaign']))
            ->assertStatus(201);

        $log = Log::query()
            ->where('action_id', Log::ACTION_CREATE_CAMPAIGN)
            ->where('logged_in_user_id', $superAdmin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKey('after');
    });
});

describe('show', function () {
    test('authenticated user with permission can view a campaign', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Campaigns/Show')
                ->has('campaign')
            );
    });

    test('unauthenticated user cannot view a campaign', function () {
        $campaign = Campaign::factory()->create();

        $this->get("/campaigns/{$campaign->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a campaign', function () {
        $user = $this->userWithNoPermissions();

        $campaign = Campaign::factory()->create();

        $this->actingAs($user)
            ->get("/campaigns/{$campaign->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent campaign', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/campaigns/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Campaigns/Edit')
                ->has('campaign')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $campaign = Campaign::factory()->create();

        $this->get("/campaigns/{$campaign->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $campaign = Campaign::factory()->create();

        $this->actingAs($user)
            ->get("/campaigns/{$campaign->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a campaign', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->create(['name' => 'Old Name']);

        $this->actingAs($superAdmin)
            ->putJson("/campaigns/{$campaign->id}", ['name' => 'New Name'])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'name' => 'New Name',
        ]);
    });

    test('patch verb also updates a campaign', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->create(['name' => 'Old Name']);

        $this->actingAs($superAdmin)
            ->patchJson("/campaigns/{$campaign->id}", ['name' => 'Patched Name'])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Patched Name']);
    });

    test('user without permission cannot update a campaign', function () {
        $user = $this->normalUser();

        $campaign = Campaign::factory()->create();

        $this->actingAs($user)
            ->putJson("/campaigns/{$campaign->id}", ['name' => 'Blocked'])
            ->assertStatus(403);
    });

    test('update fails validation when name is set to null', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->patchJson("/campaigns/{$campaign->id}", ['name' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('update fails validation when email_template_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->patchJson("/campaigns/{$campaign->id}", ['email_template_id' => 99999])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email_template_id']);
    });

    test('update fails validation when campaign_segment_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->patchJson("/campaigns/{$campaign->id}", ['campaign_segment_id' => 99999])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['campaign_segment_id']);
    });

    test('email_template_id can be updated to a different template', function () {
        $superAdmin = $this->superAdminUser();
        $newTemplate = EmailTemplate::factory()->create();

        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->patchJson("/campaigns/{$campaign->id}", ['email_template_id' => $newTemplate->id])
            ->assertStatus(200);

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'email_template_id' => $newTemplate->id,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->create(['name' => 'Original name']);
        $originalTemplateId = $campaign->email_template_id;
        $originalSegmentId = $campaign->campaign_segment_id;

        $this->actingAs($superAdmin)
            ->patchJson("/campaigns/{$campaign->id}", ['name' => 'Updated name'])
            ->assertStatus(200);

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'name' => 'Updated name',
            'email_template_id' => $originalTemplateId,
            'campaign_segment_id' => $originalSegmentId,
        ]);
    });

    test('update redirects to the show page for non-JSON requests', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->put("/campaigns/{$campaign->id}", ['name' => 'Redirected Update'])
            ->assertRedirect("/campaigns/{$campaign->id}");
    });

    test('logs campaign updates with actor id', function () {
        $actor = $this->adminUser();

        $campaign = Campaign::factory()->create(['name' => 'Old Name']);

        $this->actingAs($actor)
            ->putJson("/campaigns/{$campaign->id}", ['name' => 'New Name'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_CAMPAIGN)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a campaign', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/campaigns/{$campaign->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('campaigns', ['id' => $campaign->id]);
    });

    test('user without permission cannot soft delete a campaign', function () {
        $user = $this->normalUser();

        $campaign = Campaign::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/campaigns/{$campaign->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent campaign', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/campaigns/99999')
            ->assertStatus(404);
    });

    test('destroy records the deleting user', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/campaigns/{$campaign->id}")
            ->assertStatus(204);

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'deleted_by' => $superAdmin->id,
        ]);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted campaign', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a campaign', function () {
        $user = $this->normalUser();

        $campaign = Campaign::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/campaigns/{$campaign->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a campaign that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a campaign', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/campaigns/{$campaign->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
    });

    test('user without permission cannot force delete a campaign', function () {
        $user = $this->normalUser();

        $campaign = Campaign::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/campaigns/{$campaign->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a campaign that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/campaigns/{$campaign->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete campaigns', function () {
        $superAdmin = $this->superAdminUser();

        $campaigns = Campaign::factory()->count(3)->create();
        $ids = $campaigns->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('campaigns', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete campaigns', function () {
        $user = $this->normalUser();

        $campaigns = Campaign::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/campaigns/bulk/delete', [
                'ids' => $campaigns->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore campaigns', function () {
        $superAdmin = $this->superAdminUser();

        $campaigns = Campaign::factory()->count(3)->deleted()->create();
        $ids = $campaigns->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('campaigns', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore campaigns', function () {
        $user = $this->normalUser();

        $campaigns = Campaign::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/campaigns/bulk/restore', [
                'ids' => $campaigns->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('export', function () {
    test('authenticated user with permission can export campaigns as csv', function () {
        $superAdmin = $this->superAdminUser();

        Campaign::factory()->count(2)->create();

        $response = $this->actingAs($superAdmin)
            ->get('/campaigns/export')
            ->assertStatus(200);

        expect($response->headers->get('content-type'))->toContain('text/csv');
    });

    test('export contains the campaign name', function () {
        $superAdmin = $this->superAdminUser();

        Campaign::factory()->create(['name' => 'Exported Campaign Name']);

        $response = $this->actingAs($superAdmin)
            ->get('/campaigns/export')
            ->assertStatus(200);

        expect($response->streamedContent())->toContain('Exported Campaign Name');
    });

    test('export neutralises spreadsheet formula injection', function () {
        $superAdmin = $this->superAdminUser();

        Campaign::factory()->create(['name' => '=HYPERLINK("http://example.com")']);

        $response = $this->actingAs($superAdmin)
            ->get('/campaigns/export')
            ->assertStatus(200);

        expect($response->streamedContent())->not->toContain(',=HYPERLINK')
            ->and($response->streamedContent())->not->toMatch('/^=HYPERLINK/m');
    });

    test('unauthenticated user cannot export campaigns', function () {
        $this->get('/campaigns/export')
            ->assertRedirect('/login');
    });

    test('user without permission cannot export campaigns', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/campaigns/export')
            ->assertStatus(403);
    });
});

describe('import', function () {
    test('user without permission cannot preview an import', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/campaigns/import/preview', [
                'file' => UploadedFile::fake()->createWithContent(
                    'campaigns.csv',
                    "name,email_template_id,campaign_segment_id\nTest,1,1\n"
                ),
            ])
            ->assertStatus(403);
    });

    test('preview fails validation when file is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns/import/preview', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    test('preview fails validation when file is not a csv', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns/import/preview', [
                'file' => UploadedFile::fake()->create('campaigns.pdf', 100, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    test('preview returns a token without persisting any campaigns', function () {
        $superAdmin = $this->superAdminUser();

        $templateId = EmailTemplate::factory()->create()->id;
        $segmentId = CampaignSegment::factory()->create()->id;

        $this->actingAs($superAdmin)
            ->postJson('/campaigns/import/preview', [
                'file' => UploadedFile::fake()->createWithContent(
                    'campaigns.csv',
                    "name,email_template_id,campaign_segment_id\nImported Campaign,{$templateId},{$segmentId}\n"
                ),
            ])
            ->assertStatus(200)
            ->assertJsonStructure(['token']);

        $this->assertDatabaseMissing('campaigns', ['name' => 'Imported Campaign']);
    });

    test('commit fails validation when token is not a uuid', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns/import/commit', [
                'token' => 'not-a-uuid',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    });

    test('commit fails validation when token is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/campaigns/import/commit', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    });

    test('user without permission cannot commit an import', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/campaigns/import/commit', [
                'token' => Str::uuid()->toString(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted campaigns', function () {
        $superAdmin = $this->superAdminUser();

        Campaign::factory()->count(2)->create();
        $trashed = Campaign::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/campaigns')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Campaigns/Index')
                ->has('campaigns.data', 2)
            );

        $this->assertSoftDeleted('campaigns', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted campaign', function () {
        $superAdmin = $this->superAdminUser();

        $campaign = Campaign::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}")
            ->assertStatus(404);
    });

    test('index can include trashed campaigns when requested', function () {
        $superAdmin = $this->superAdminUser();

        Campaign::factory()->count(2)->create();
        Campaign::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/campaigns?trashed=with')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Campaigns/Index')
                ->has('campaigns.data', 3)
            );
    });
});

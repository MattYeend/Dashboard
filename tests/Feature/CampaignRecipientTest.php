<?php

declare(strict_types=1);

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Log;
use App\Models\Organisation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

describe('index', function () {
    test('authenticated user with permission can list campaign recipients', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        CampaignRecipient::factory()->count(3)->create(['campaign_id' => $campaign->id]);

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('CampaignRecipients/Index')
                ->has('campaignRecipients.data', 3)
                ->has('permissions_meta')
            );
    });

    test('index only lists recipients of the requested campaign', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();
        $otherCampaign = Campaign::factory()->create();

        CampaignRecipient::factory()->count(2)->create(['campaign_id' => $campaign->id]);
        CampaignRecipient::factory()->count(3)->create(['campaign_id' => $otherCampaign->id]);

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('CampaignRecipients/Index')
                ->has('campaignRecipients.data', 2)
            );
    });

    test('unauthenticated user cannot list campaign recipients', function () {
        $campaign = Campaign::factory()->create();

        $this->get("/campaigns/{$campaign->id}/campaign-recipients")
            ->assertRedirect('/login');
    });

    test('user without permission cannot list campaign recipients', function () {
        $user = $this->userWithNoPermissions();
        $campaign = Campaign::factory()->create();

        $this->actingAs($user)
            ->get("/campaigns/{$campaign->id}/campaign-recipients")
            ->assertStatus(403);
    });

    test('index can be searched by email', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        CampaignRecipient::factory()->create(['campaign_id' => $campaign->id, 'email' => 'spring@example.com']);
        CampaignRecipient::factory()->create(['campaign_id' => $campaign->id, 'email' => 'autumn@example.com']);

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients?search=spring")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('CampaignRecipients/Index')
                ->has('campaignRecipients.data', 1)
            );
    });

    test('index can be filtered by status', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        CampaignRecipient::factory()->count(2)->create(['campaign_id' => $campaign->id]);
        CampaignRecipient::factory()->sent()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients?status=sent")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('CampaignRecipients/Index')
                ->has('campaignRecipients.data', 1)
            );
    });

    test('index ignores an unknown status value', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        CampaignRecipient::factory()->count(2)->create(['campaign_id' => $campaign->id]);

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients?status=bogus")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->has('campaignRecipients.data', 2)
            );
    });

    test('index never exposes the token', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        CampaignRecipient::factory()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->missing('campaignRecipients.data.0.token')
            );
    });

    test('index returns 404 for a campaign in another organisation', function () {
        $superAdmin = $this->superAdminUser();
        $otherOrganisation = Organisation::factory()->create();
        $campaign = Campaign::factory()->create(['organisation_id' => $otherOrganisation->id]);

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients")
            ->assertStatus(404);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a campaign recipient', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients/{$campaignRecipient->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('CampaignRecipients/Show')
                ->has('campaignRecipient')
                ->missing('campaignRecipient.token')
            );
    });

    test('unauthenticated user cannot view a campaign recipient', function () {
        $campaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->create(['campaign_id' => $campaign->id]);

        $this->get("/campaigns/{$campaign->id}/campaign-recipients/{$campaignRecipient->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a campaign recipient', function () {
        $user = $this->userWithNoPermissions();
        $campaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($user)
            ->get("/campaigns/{$campaign->id}/campaign-recipients/{$campaignRecipient->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent campaign recipient', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients/99999")
            ->assertStatus(404);
    });

    test('show returns 404 when the recipient belongs to a different campaign', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();
        $otherCampaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->create(['campaign_id' => $otherCampaign->id]);

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients/{$campaignRecipient->id}")
            ->assertStatus(404);
    });

    test('show returns 404 for a soft-deleted campaign recipient', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->deleted()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients/{$campaignRecipient->id}")
            ->assertStatus(404);
    });
});

describe('read-only resource', function () {
    test('create, store, edit and update routes do not exist', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->create(['campaign_id' => $campaign->id]);
        $base = "/campaigns/{$campaign->id}/campaign-recipients";

        $responses = [
            $this->actingAs($superAdmin)->postJson($base, ['email' => 'new@example.com']),
            $this->actingAs($superAdmin)->putJson("{$base}/{$campaignRecipient->id}", ['meta' => []]),
            $this->actingAs($superAdmin)->patchJson("{$base}/{$campaignRecipient->id}", ['meta' => []]),
            $this->actingAs($superAdmin)->get("{$base}/{$campaignRecipient->id}/edit"),
        ];

        foreach ($responses as $response) {
            expect($response->status())->toBeIn([404, 405]);
        }

        $this->assertDatabaseCount('campaign_recipients', 1);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a campaign recipient', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/campaigns/{$campaign->id}/campaign-recipients/{$campaignRecipient->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('campaign_recipients', ['id' => $campaignRecipient->id]);
    });

    test('destroy records the deleting user and an audit entry', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/campaigns/{$campaign->id}/campaign-recipients/{$campaignRecipient->id}")
            ->assertStatus(204);

        $this->assertDatabaseHas('campaign_recipients', [
            'id' => $campaignRecipient->id,
            'deleted_by' => $superAdmin->id,
        ]);

        $log = Log::query()
            ->where('action_id', Log::ACTION_DELETE_CAMPAIGN_RECIPIENT)
            ->where('logged_in_user_id', $superAdmin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKey('before')
            ->and(json_encode($log->data))->not->toContain($campaignRecipient->token);
    });

    test('user without permission cannot soft delete a campaign recipient', function () {
        $user = $this->normalUser();
        $campaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($user)
            ->deleteJson("/campaigns/{$campaign->id}/campaign-recipients/{$campaignRecipient->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent campaign recipient', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/campaigns/{$campaign->id}/campaign-recipients/99999")
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted campaign recipient', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->deleted()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/campaign-recipients/{$campaignRecipient->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('campaign_recipients', [
            'id' => $campaignRecipient->id,
            'deleted_at' => null,
            'restored_by' => $superAdmin->id,
        ]);
    });

    test('user without permission cannot restore a campaign recipient', function () {
        $user = $this->normalUser();
        $campaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->deleted()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($user)
            ->postJson("/campaigns/{$campaign->id}/campaign-recipients/{$campaignRecipient->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a campaign recipient that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/campaign-recipients/{$campaignRecipient->id}/restore")
            ->assertStatus(404);
    });

    test('restore returns 404 when the recipient belongs to a different campaign', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();
        $otherCampaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->deleted()->create(['campaign_id' => $otherCampaign->id]);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/campaign-recipients/{$campaignRecipient->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a campaign recipient', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->deleted()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/campaigns/{$campaign->id}/campaign-recipients/{$campaignRecipient->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('campaign_recipients', ['id' => $campaignRecipient->id]);
    });

    test('user without permission cannot force delete a campaign recipient', function () {
        $user = $this->normalUser();
        $campaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->deleted()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($user)
            ->deleteJson("/campaigns/{$campaign->id}/campaign-recipients/{$campaignRecipient->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a campaign recipient that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/campaigns/{$campaign->id}/campaign-recipients/{$campaignRecipient->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete campaign recipients', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        $campaignRecipients = CampaignRecipient::factory()->count(3)->create(['campaign_id' => $campaign->id]);
        $ids = $campaignRecipients->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/campaign-recipients/bulk/delete", ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('campaign_recipients', ['id' => $id]);
        }
    });

    test('bulk delete skips recipients of another campaign', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();
        $otherCampaign = Campaign::factory()->create();
        $otherRecipient = CampaignRecipient::factory()->create(['campaign_id' => $otherCampaign->id]);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/campaign-recipients/bulk/delete", ['ids' => [$otherRecipient->id]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [$otherRecipient->id],
            ]);

        $this->assertDatabaseHas('campaign_recipients', [
            'id' => $otherRecipient->id,
            'deleted_at' => null,
        ]);
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/campaign-recipients/bulk/delete", ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/campaign-recipients/bulk/delete", ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete campaign recipients', function () {
        $user = $this->normalUser();
        $campaign = Campaign::factory()->create();
        $campaignRecipients = CampaignRecipient::factory()->count(2)->create(['campaign_id' => $campaign->id]);

        $this->actingAs($user)
            ->postJson("/campaigns/{$campaign->id}/campaign-recipients/bulk/delete", [
                'ids' => $campaignRecipients->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore campaign recipients', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        $campaignRecipients = CampaignRecipient::factory()->count(3)->deleted()->create(['campaign_id' => $campaign->id]);
        $ids = $campaignRecipients->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/campaign-recipients/bulk/restore", ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('campaign_recipients', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/campaign-recipients/bulk/restore", ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/campaign-recipients/bulk/restore", ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore campaign recipients', function () {
        $user = $this->normalUser();
        $campaign = Campaign::factory()->create();
        $campaignRecipients = CampaignRecipient::factory()->count(2)->deleted()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($user)
            ->postJson("/campaigns/{$campaign->id}/campaign-recipients/bulk/restore", [
                'ids' => $campaignRecipients->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('export', function () {
    test('authenticated user with permission can export campaign recipients as csv', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        CampaignRecipient::factory()->count(2)->create(['campaign_id' => $campaign->id]);

        $response = $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients/export")
            ->assertStatus(200);

        expect($response->headers->get('content-type'))->toContain('text/csv');
    });

    test('export contains the email and never the token', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        $campaignRecipient = CampaignRecipient::factory()->create([
            'campaign_id' => $campaign->id,
            'email' => 'exported@example.com',
        ]);

        $content = $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients/export")
            ->assertStatus(200)
            ->streamedContent();

        expect($content)->toContain('exported@example.com')
            ->and($content)->not->toContain($campaignRecipient->token)
            ->and($content)->not->toContain('token');
    });

    test('export only contains recipients of the requested campaign', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();
        $otherCampaign = Campaign::factory()->create();

        CampaignRecipient::factory()->create(['campaign_id' => $campaign->id, 'email' => 'mine@example.com']);
        CampaignRecipient::factory()->create(['campaign_id' => $otherCampaign->id, 'email' => 'theirs@example.com']);

        $content = $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients/export")
            ->assertStatus(200)
            ->streamedContent();

        expect($content)->toContain('mine@example.com')
            ->and($content)->not->toContain('theirs@example.com');
    });

    test('export neutralises spreadsheet formula injection', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        CampaignRecipient::factory()->create([
            'campaign_id' => $campaign->id,
            'name' => '=HYPERLINK("http://example.com")',
        ]);

        $content = $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients/export")
            ->assertStatus(200)
            ->streamedContent();

        expect($content)->not->toContain(',=HYPERLINK')
            ->and($content)->not->toMatch('/^=HYPERLINK/m');
    });

    test('unauthenticated user cannot export campaign recipients', function () {
        $campaign = Campaign::factory()->create();

        $this->get("/campaigns/{$campaign->id}/campaign-recipients/export")
            ->assertRedirect('/login');
    });

    test('user without permission cannot export campaign recipients', function () {
        $user = $this->normalUser();
        $campaign = Campaign::factory()->create();

        $this->actingAs($user)
            ->get("/campaigns/{$campaign->id}/campaign-recipients/export")
            ->assertStatus(403);
    });

    test('export records an audit entry', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients/export")
            ->assertStatus(200)
            ->streamedContent();

        $log = Log::query()
            ->where('action_id', Log::ACTION_EXPORT_CAMPAIGN_RECIPIENT)
            ->where('logged_in_user_id', $superAdmin->id)
            ->first();

        expect($log)->not->toBeNull();
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted campaign recipients', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        CampaignRecipient::factory()->count(2)->create(['campaign_id' => $campaign->id]);
        CampaignRecipient::factory()->deleted()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('CampaignRecipients/Index')
                ->has('campaignRecipients.data', 2)
            );
    });

    test('index can include trashed campaign recipients when requested', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = Campaign::factory()->create();

        CampaignRecipient::factory()->count(2)->create(['campaign_id' => $campaign->id]);
        CampaignRecipient::factory()->deleted()->create(['campaign_id' => $campaign->id]);

        $this->actingAs($superAdmin)
            ->get("/campaigns/{$campaign->id}/campaign-recipients?trashed=with")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('CampaignRecipients/Index')
                ->has('campaignRecipients.data', 3)
            );
    });
});

describe('model', function () {
    test('token is hidden from serialisation', function () {
        $campaignRecipient = CampaignRecipient::factory()->create();

        expect($campaignRecipient->toArray())->not->toHaveKey('token')
            ->and($campaignRecipient->token)->toHaveLength(40);
    });

    test('token is unique per recipient', function () {
        $campaign = Campaign::factory()->create();

        $first = CampaignRecipient::factory()->create(['campaign_id' => $campaign->id]);
        $second = CampaignRecipient::factory()->create(['campaign_id' => $campaign->id]);

        expect($first->token)->not->toBe($second->token);
    });

    test('the same email cannot be added twice to one campaign', function () {
        $campaign = Campaign::factory()->create();

        CampaignRecipient::factory()->create(['campaign_id' => $campaign->id, 'email' => 'dupe@example.com']);

        expect(fn () => CampaignRecipient::factory()->create([
            'campaign_id' => $campaign->id,
            'email' => 'dupe@example.com',
        ]))->toThrow(Illuminate\Database\QueryException::class);
    });
});
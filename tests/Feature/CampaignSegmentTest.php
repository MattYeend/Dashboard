<?php

// declare(strict_types=1);

// use App\Models\Campaign;
// use App\Models\CampaignSegment;
// use App\Models\CampaignStatus;
// use App\Models\Log;
// use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
// use Illuminate\Http\UploadedFile;
// use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Str;
// use Inertia\Testing\AssertableInertia as Assert;
// use Tests\Concerns\CreatesUsers;

// uses(
//     LazilyRefreshDatabase::class,
//     CreatesUsers::class,
// );

// if (! function_exists('campaignSegmentPayload')) {
//     /**
//      * Build a valid campaign segment payload.
//      *
//      * @param  array<string, mixed>  $overrides
//      * @return array<string, mixed>
//      */
//     function campaignSegmentPayload(array $overrides = []): array
//     {
//         return array_merge([
//             'name' => 'Spring Subscribers',
//             'description' => 'Contacts who opted in this spring.',
//             'criteria' => [
//                 ['field' => 'email_subscribed', 'operator' => 'equals', 'value' => true],
//             ],
//         ], $overrides);
//     }
// }

// if (! function_exists('campaignUsingSegment')) {
//     /**
//      * Create a campaign that uses the given segment with the given status key.
//      */
//     function campaignUsingSegment(CampaignSegment $segment, string $statusKey): Campaign
//     {
//         $status = CampaignStatus::query()->where('system_key', $statusKey)->first()
//             ?? CampaignStatus::factory()->create(['system_key' => $statusKey]);

//         return Campaign::factory()->create([
//             'campaign_segment_id' => $segment->id,
//             'campaign_status_id' => $status->id,
//         ]);
//     }
// }

// describe('index', function () {
//     test('authenticated user with permission can list campaign segments', function () {
//         CampaignSegment::factory()->count(3)->create();

//         $this->actingAs($this->superAdminUser())
//             ->get('/campaign-segments')
//             ->assertStatus(200)
//             ->assertInertia(fn (Assert $page) => $page
//                 ->component('CampaignSegments/Index', false)
//                 ->has('campaignSegments.data', 3)
//                 ->has('permissions_meta')
//             );
//     });

//     test('unauthenticated user is redirected to login', function () {
//         $this->get('/campaign-segments')->assertRedirect('/login');
//     });

//     test('user without permission cannot list campaign segments', function () {
//         $this->actingAs($this->userWithNoPermissions())
//             ->get('/campaign-segments')
//             ->assertStatus(403);
//     });

//     test('index can be searched by name', function () {
//         CampaignSegment::factory()->create(['name' => 'Spring Offer']);
//         CampaignSegment::factory()->create(['name' => 'Autumn Newsletter']);

//         $this->actingAs($this->superAdminUser())
//             ->get('/campaign-segments?search=Spring')
//             ->assertStatus(200)
//             ->assertInertia(fn (Assert $page) => $page
//                 ->component('CampaignSegments/Index', false)
//                 ->has('campaignSegments.data', 1)
//             );
//     });

//     test('search treats percent and underscore literally', function () {
//         CampaignSegment::factory()->create(['name' => 'Plain name']);

//         $this->actingAs($this->superAdminUser())
//             ->get('/campaign-segments?search=%25')
//             ->assertStatus(200)
//             ->assertInertia(fn (Assert $page) => $page
//                 ->component('CampaignSegments/Index', false)
//                 ->has('campaignSegments.data', 0)
//             );
//     });

//     test('array valued query parameters do not cause a server error', function () {
//         $this->actingAs($this->superAdminUser())
//             ->get('/campaign-segments?search[]=x&sort_by[]=name&per_page[]=5')
//             ->assertStatus(200);
//     });

//     test('unknown sort column falls back safely', function () {
//         CampaignSegment::factory()->count(2)->create();

//         $this->actingAs($this->superAdminUser())
//             ->get('/campaign-segments?sort_by=password;DROP TABLE users&sort_direction=sideways')
//             ->assertStatus(200);
//     });
// });

// describe('create', function () {
//     test('authenticated user with permission can view the create form', function () {
//         $this->actingAs($this->superAdminUser())
//             ->get('/campaign-segments/create')
//             ->assertStatus(200)
//             ->assertInertia(fn (Assert $page) => $page
//                 ->component('CampaignSegments/Create', false)
//                 ->has('fieldOptions')
//                 ->has('operatorOptions')
//             );
//     });

//     test('unauthenticated user cannot view the create form', function () {
//         $this->get('/campaign-segments/create')->assertRedirect('/login');
//     });

//     test('user without permission cannot view the create form', function () {
//         $this->actingAs($this->normalUser())
//             ->get('/campaign-segments/create')
//             ->assertStatus(403);
//     });
// });

// describe('store', function () {
//     test('authenticated user with permission can create a campaign segment', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments', campaignSegmentPayload())
//             ->assertStatus(201)
//             ->assertJsonFragment(['name' => 'Spring Subscribers']);

//         expect(CampaignSegment::count())->toBe(1);
//     });

//     test('criteria values are stored as their declared types', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments', campaignSegmentPayload([
//                 'criteria' => [
//                     ['field' => 'email_subscribed', 'operator' => 'equals', 'value' => 'true'],
//                     ['field' => 'inactive_days', 'operator' => 'greater_than', 'value' => '90'],
//                 ],
//             ]))
//             ->assertStatus(201);

//         $segment = CampaignSegment::firstOrFail();

//         expect($segment->criteria[0]['value'])->toBeTrue()
//             ->and($segment->criteria[1]['value'])->toBe(90);
//     });

//     test('store redirects to the show page for non-JSON requests', function () {
//         $this->actingAs($this->superAdminUser())
//             ->post('/campaign-segments', campaignSegmentPayload(['name' => 'Redirect Segment']))
//             ->assertRedirect();

//         expect(CampaignSegment::where('name', 'Redirect Segment')->exists())->toBeTrue();
//     });

//     test('user without permission cannot create a campaign segment', function () {
//         $this->actingAs($this->normalUser())
//             ->postJson('/campaign-segments', campaignSegmentPayload())
//             ->assertStatus(403);
//     });

//     test('unauthenticated user cannot create a campaign segment', function () {
//         $this->postJson('/campaign-segments', campaignSegmentPayload())
//             ->assertStatus(401);
//     });

//     test('validation fails for an empty payload', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments', [])
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['name']);
//     });

//     test('validation fails when name exceeds the maximum length', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments', campaignSegmentPayload(['name' => str_repeat('a', 256)]))
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['name']);
//     });

//     test('validation fails for a field outside the allow-list', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments', campaignSegmentPayload([
//                 'criteria' => [
//                     ['field' => 'password; DROP TABLE users', 'operator' => 'equals', 'value' => 1],
//                 ],
//             ]))
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['criteria.0.field']);
//     });

//     test('validation fails for an operator outside the allow-list', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments', campaignSegmentPayload([
//                 'criteria' => [
//                     ['field' => 'email_subscribed', 'operator' => '= 1 OR 1', 'value' => true],
//                 ],
//             ]))
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['criteria.0.operator']);
//     });

//     test('validation fails when the operator is not allowed for the field', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments', campaignSegmentPayload([
//                 'criteria' => [
//                     ['field' => 'email_subscribed', 'operator' => 'greater_than', 'value' => true],
//                 ],
//             ]))
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['criteria.0.operator']);
//     });

//     test('validation fails when the value has the wrong type for the field', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments', campaignSegmentPayload([
//                 'criteria' => [
//                     ['field' => 'inactive_days', 'operator' => 'greater_than', 'value' => 'ninety'],
//                 ],
//             ]))
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['criteria.0.value']);
//     });

//     test('validation fails when a day value is out of range', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments', campaignSegmentPayload([
//                 'criteria' => [
//                     ['field' => 'inactive_days', 'operator' => 'greater_than', 'value' => 99999],
//                 ],
//             ]))
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['criteria.0.value']);
//     });

//     test('validation fails when a criterion carries unknown keys', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments', campaignSegmentPayload([
//                 'criteria' => [
//                     ['field' => 'email_subscribed', 'operator' => 'equals', 'value' => true, 'raw_sql' => '1=1'],
//                 ],
//             ]))
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['criteria.0']);
//     });

//     test('validation fails when there are too many criteria', function () {
//         $criteria = array_fill(0, 21, ['field' => 'email_subscribed', 'operator' => 'equals', 'value' => true]);

//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments', campaignSegmentPayload(['criteria' => $criteria]))
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['criteria']);
//     });

//     test('duplicate name in the same organisation is rejected', function () {
//         CampaignSegment::factory()->create(['name' => 'Spring Subscribers']);

//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments', campaignSegmentPayload())
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['name']);
//     });

//     test('a soft deleted segment does not block re-using its name', function () {
//         CampaignSegment::factory()->deleted()->create(['name' => 'Spring Subscribers']);

//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments', campaignSegmentPayload())
//             ->assertStatus(201);
//     });

//     test('system managed fields in the request are ignored', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments', campaignSegmentPayload([
//                 'estimated_recipients_count' => 9999,
//                 'last_refreshed_at' => '2020-01-01 00:00:00',
//                 'created_by' => 12345,
//                 'organisation_id' => 12345,
//                 'meta' => ['injected' => true],
//             ]))
//             ->assertStatus(201);

//         $segment = CampaignSegment::firstOrFail();

//         expect($segment->estimated_recipients_count)->toBe(0)
//             ->and($segment->last_refreshed_at)->toBeNull()
//             ->and($segment->created_by)->not->toBe(12345)
//             ->and($segment->organisation_id)->not->toBe(12345)
//             ->and($segment->meta)->toBeNull();
//     });

//     test('logs campaign segment creation with actor id', function () {
//         $superAdmin = $this->superAdminUser();

//         $this->actingAs($superAdmin)
//             ->postJson('/campaign-segments', campaignSegmentPayload())
//             ->assertStatus(201);

//         $log = Log::query()
//             ->where('action_id', Log::ACTION_CREATE_CAMPAIGN_SEGMENT)
//             ->where('logged_in_user_id', $superAdmin->id)
//             ->first();

//         expect($log)->not->toBeNull()
//             ->and($log->data)->toHaveKey('after');
//     });
// });

// describe('show', function () {
//     test('authenticated user with permission can view a campaign segment', function () {
//         $segment = CampaignSegment::factory()->create();

//         $this->actingAs($this->superAdminUser())
//             ->get("/campaign-segments/{$segment->id}")
//             ->assertStatus(200)
//             ->assertInertia(fn (Assert $page) => $page
//                 ->component('CampaignSegments/Show', false)
//                 ->has('campaignSegment')
//             );
//     });

//     test('unauthenticated user cannot view a campaign segment', function () {
//         $segment = CampaignSegment::factory()->create();

//         $this->get("/campaign-segments/{$segment->id}")->assertRedirect('/login');
//     });

//     test('user without permission cannot view a campaign segment', function () {
//         $segment = CampaignSegment::factory()->create();

//         $this->actingAs($this->userWithNoPermissions())
//             ->get("/campaign-segments/{$segment->id}")
//             ->assertStatus(403);
//     });

//     test('show returns 404 for a non-existent campaign segment', function () {
//         $this->actingAs($this->superAdminUser())
//             ->get('/campaign-segments/99999')
//             ->assertStatus(404);
//     });

//     test('show returns 404 for a soft-deleted campaign segment', function () {
//         $segment = CampaignSegment::factory()->deleted()->create();

//         $this->actingAs($this->superAdminUser())
//             ->get("/campaign-segments/{$segment->id}")
//             ->assertStatus(404);
//     });
// });

// describe('edit', function () {
//     test('authenticated user with permission can view the edit form', function () {
//         $segment = CampaignSegment::factory()->create();

//         $this->actingAs($this->superAdminUser())
//             ->get("/campaign-segments/{$segment->id}/edit")
//             ->assertStatus(200)
//             ->assertInertia(fn (Assert $page) => $page
//                 ->component('CampaignSegments/Edit', false)
//                 ->has('campaignSegment')
//                 ->has('fieldOptions')
//             );
//     });

//     test('unauthenticated user cannot view the edit form', function () {
//         $segment = CampaignSegment::factory()->create();

//         $this->get("/campaign-segments/{$segment->id}/edit")->assertRedirect('/login');
//     });

//     test('user without permission cannot view the edit form', function () {
//         $segment = CampaignSegment::factory()->create();

//         $this->actingAs($this->normalUser())
//             ->get("/campaign-segments/{$segment->id}/edit")
//             ->assertStatus(403);
//     });
// });

// describe('update', function () {
//     test('authenticated user with permission can update a campaign segment', function () {
//         $segment = CampaignSegment::factory()->create(['name' => 'Old Name']);

//         $this->actingAs($this->superAdminUser())
//             ->putJson("/campaign-segments/{$segment->id}", ['name' => 'New Name'])
//             ->assertStatus(200)
//             ->assertJsonFragment(['name' => 'New Name']);

//         $this->assertDatabaseHas('campaign_segments', ['id' => $segment->id, 'name' => 'New Name']);
//     });

//     test('patch verb also updates a campaign segment', function () {
//         $segment = CampaignSegment::factory()->create(['name' => 'Old Name']);

//         $this->actingAs($this->superAdminUser())
//             ->patchJson("/campaign-segments/{$segment->id}", ['name' => 'Patched Name'])
//             ->assertStatus(200)
//             ->assertJsonFragment(['name' => 'Patched Name']);
//     });

//     test('user without permission cannot update a campaign segment', function () {
//         $segment = CampaignSegment::factory()->create();

//         $this->actingAs($this->normalUser())
//             ->putJson("/campaign-segments/{$segment->id}", ['name' => 'Blocked'])
//             ->assertStatus(403);
//     });

//     test('update fails validation when name is set to null', function () {
//         $segment = CampaignSegment::factory()->create();

//         $this->actingAs($this->superAdminUser())
//             ->patchJson("/campaign-segments/{$segment->id}", ['name' => null])
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['name']);
//     });

//     test('a segment can keep its own name on update', function () {
//         $segment = CampaignSegment::factory()->create(['name' => 'Same Name']);

//         $this->actingAs($this->superAdminUser())
//             ->putJson("/campaign-segments/{$segment->id}", ['name' => 'Same Name'])
//             ->assertStatus(200);
//     });

//     test('update rejects a name used by another segment', function () {
//         CampaignSegment::factory()->create(['name' => 'Taken']);
//         $segment = CampaignSegment::factory()->create(['name' => 'Free']);

//         $this->actingAs($this->superAdminUser())
//             ->putJson("/campaign-segments/{$segment->id}", ['name' => 'Taken'])
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['name']);
//     });

//     test('update validates criteria against the allow-list', function () {
//         $segment = CampaignSegment::factory()->create();

//         $this->actingAs($this->superAdminUser())
//             ->patchJson("/campaign-segments/{$segment->id}", [
//                 'criteria' => [['field' => 'users.password', 'operator' => 'equals', 'value' => 1]],
//             ])
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['criteria.0.field']);
//     });

//     test('omitted fields are not cleared on update', function () {
//         $segment = CampaignSegment::factory()->create(['name' => 'Original', 'description' => 'Keep me']);
//         $originalCriteria = $segment->criteria;

//         $this->actingAs($this->superAdminUser())
//             ->patchJson("/campaign-segments/{$segment->id}", ['name' => 'Updated'])
//             ->assertStatus(200);

//         $fresh = $segment->fresh();

//         expect($fresh->name)->toBe('Updated')
//             ->and($fresh->description)->toBe('Keep me')
//             ->and($fresh->criteria)->toBe($originalCriteria);
//     });

//     test('system managed fields cannot be changed through update', function () {
//         $segment = CampaignSegment::factory()->create(['estimated_recipients_count' => 10]);

//         $this->actingAs($this->superAdminUser())
//             ->patchJson("/campaign-segments/{$segment->id}", [
//                 'name' => 'Renamed',
//                 'estimated_recipients_count' => 9999,
//                 'last_refreshed_at' => '2020-01-01 00:00:00',
//             ])
//             ->assertStatus(200);

//         $fresh = $segment->fresh();

//         expect($fresh->estimated_recipients_count)->toBe(10)
//             ->and($fresh->last_refreshed_at)->toBeNull();
//     });

//     test('update redirects to the show page for non-JSON requests', function () {
//         $segment = CampaignSegment::factory()->create();

//         $this->actingAs($this->superAdminUser())
//             ->put("/campaign-segments/{$segment->id}", ['name' => 'Redirected Update'])
//             ->assertRedirect("/campaign-segments/{$segment->id}");
//     });

//     test('logs campaign segment updates with actor id', function () {
//         $actor = $this->adminUser();
//         $segment = CampaignSegment::factory()->create(['name' => 'Old Name']);

//         $this->actingAs($actor)
//             ->putJson("/campaign-segments/{$segment->id}", ['name' => 'New Name'])
//             ->assertOk();

//         $log = Log::query()
//             ->where('action_id', Log::ACTION_UPDATE_CAMPAIGN_SEGMENT)
//             ->where('logged_in_user_id', $actor->id)
//             ->first();

//         expect($log)->not->toBeNull()
//             ->and($log->data)->toHaveKeys(['before', 'after']);
//     });
// });

// describe('destroy', function () {
//     test('authenticated user with permission can soft delete a campaign segment', function () {
//         $segment = CampaignSegment::factory()->create();

//         $this->actingAs($this->superAdminUser())
//             ->deleteJson("/campaign-segments/{$segment->id}")
//             ->assertStatus(204);

//         $this->assertSoftDeleted('campaign_segments', ['id' => $segment->id]);
//     });

//     test('user without permission cannot soft delete a campaign segment', function () {
//         $segment = CampaignSegment::factory()->create();

//         $this->actingAs($this->normalUser())
//             ->deleteJson("/campaign-segments/{$segment->id}")
//             ->assertStatus(403);
//     });

//     test('destroy returns 404 for a non-existent campaign segment', function () {
//         $this->actingAs($this->superAdminUser())
//             ->deleteJson('/campaign-segments/99999')
//             ->assertStatus(404);
//     });

//     test('destroy records the deleting user', function () {
//         $superAdmin = $this->superAdminUser();
//         $segment = CampaignSegment::factory()->create();

//         $this->actingAs($superAdmin)
//             ->deleteJson("/campaign-segments/{$segment->id}")
//             ->assertStatus(204);

//         $this->assertDatabaseHas('campaign_segments', [
//             'id' => $segment->id,
//             'deleted_by' => $superAdmin->id,
//         ]);
//     });

//     test('a segment used by a scheduled campaign cannot be deleted', function () {
//         $segment = CampaignSegment::factory()->create();
//         campaignUsingSegment($segment, 'scheduled');

//         $this->actingAs($this->superAdminUser())
//             ->deleteJson("/campaign-segments/{$segment->id}")
//             ->assertStatus(403);

//         $this->assertNotSoftDeleted('campaign_segments', ['id' => $segment->id]);
//     });

//     test('a segment used by a sending campaign cannot be deleted', function () {
//         $segment = CampaignSegment::factory()->create();
//         campaignUsingSegment($segment, 'sending');

//         $this->actingAs($this->superAdminUser())
//             ->deleteJson("/campaign-segments/{$segment->id}")
//             ->assertStatus(403);
//     });
// });

// describe('restore', function () {
//     test('authenticated user with permission can restore a soft-deleted campaign segment', function () {
//         $segment = CampaignSegment::factory()->deleted()->create();

//         $this->actingAs($this->superAdminUser())
//             ->postJson("/campaign-segments/{$segment->id}/restore")
//             ->assertStatus(204);

//         $this->assertDatabaseHas('campaign_segments', ['id' => $segment->id, 'deleted_at' => null]);
//     });

//     test('user without permission cannot restore a campaign segment', function () {
//         $segment = CampaignSegment::factory()->deleted()->create();

//         $this->actingAs($this->normalUser())
//             ->postJson("/campaign-segments/{$segment->id}/restore")
//             ->assertStatus(403);
//     });

//     test('restore returns 404 for a campaign segment that is not soft-deleted', function () {
//         $segment = CampaignSegment::factory()->create();

//         $this->actingAs($this->superAdminUser())
//             ->postJson("/campaign-segments/{$segment->id}/restore")
//             ->assertStatus(404);
//     });
// });

// describe('force delete', function () {
//     test('authenticated user with permission can force delete a campaign segment', function () {
//         $segment = CampaignSegment::factory()->deleted()->create();

//         $this->actingAs($this->superAdminUser())
//             ->deleteJson("/campaign-segments/{$segment->id}/force")
//             ->assertStatus(204);

//         $this->assertDatabaseMissing('campaign_segments', ['id' => $segment->id]);
//     });

//     test('user without permission cannot force delete a campaign segment', function () {
//         $segment = CampaignSegment::factory()->deleted()->create();

//         $this->actingAs($this->normalUser())
//             ->deleteJson("/campaign-segments/{$segment->id}/force")
//             ->assertStatus(403);
//     });

//     test('force delete returns 404 for a campaign segment that is not soft-deleted', function () {
//         $segment = CampaignSegment::factory()->create();

//         $this->actingAs($this->superAdminUser())
//             ->deleteJson("/campaign-segments/{$segment->id}/force")
//             ->assertStatus(404);
//     });

//     test('a segment still referenced by a soft-deleted campaign cannot be force deleted', function () {
//         $segment = CampaignSegment::factory()->deleted()->create();
//         $campaign = campaignUsingSegment($segment, 'draft');
//         $campaign->delete();

//         $this->actingAs($this->superAdminUser())
//             ->deleteJson("/campaign-segments/{$segment->id}/force")
//             ->assertStatus(403);

//         $this->assertDatabaseHas('campaign_segments', ['id' => $segment->id]);
//     });
// });

// describe('bulk delete', function () {
//     test('authenticated user with permission can bulk soft delete campaign segments', function () {
//         $segments = CampaignSegment::factory()->count(3)->create();
//         $ids = $segments->pluck('id')->all();

//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments/bulk/delete', ['ids' => $ids])
//             ->assertStatus(200)
//             ->assertJson(['deleted' => $ids, 'skipped' => []]);

//         foreach ($ids as $id) {
//             $this->assertSoftDeleted('campaign_segments', ['id' => $id]);
//         }
//     });

//     test('bulk delete fails validation with an empty ids array', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments/bulk/delete', ['ids' => []])
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['ids']);
//     });

//     test('bulk delete skips non-existent ids', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments/bulk/delete', ['ids' => [99999]])
//             ->assertStatus(200)
//             ->assertJson(['deleted' => [], 'skipped' => [99999]]);
//     });

//     test('bulk delete skips segments locked by an active campaign', function () {
//         $free = CampaignSegment::factory()->create();
//         $locked = CampaignSegment::factory()->create();
//         campaignUsingSegment($locked, 'scheduled');

//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments/bulk/delete', ['ids' => [$free->id, $locked->id]])
//             ->assertStatus(200)
//             ->assertJson(['deleted' => [$free->id], 'skipped' => [$locked->id]]);

//         $this->assertNotSoftDeleted('campaign_segments', ['id' => $locked->id]);
//     });

//     test('user without permission cannot bulk delete campaign segments', function () {
//         $segments = CampaignSegment::factory()->count(2)->create();

//         $this->actingAs($this->normalUser())
//             ->postJson('/campaign-segments/bulk/delete', ['ids' => $segments->pluck('id')->all()])
//             ->assertStatus(403);
//     });
// });

// describe('bulk restore', function () {
//     test('authenticated user with permission can bulk restore campaign segments', function () {
//         $segments = CampaignSegment::factory()->count(3)->deleted()->create();
//         $ids = $segments->pluck('id')->all();

//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments/bulk/restore', ['ids' => $ids])
//             ->assertStatus(200)
//             ->assertJson(['restored' => $ids, 'skipped' => []]);

//         foreach ($ids as $id) {
//             $this->assertDatabaseHas('campaign_segments', ['id' => $id, 'deleted_at' => null]);
//         }
//     });

//     test('bulk restore fails validation with an empty ids array', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments/bulk/restore', ['ids' => []])
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['ids']);
//     });

//     test('bulk restore skips non-existent ids', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments/bulk/restore', ['ids' => [99999]])
//             ->assertStatus(200)
//             ->assertJson(['restored' => [], 'skipped' => [99999]]);
//     });

//     test('user without permission cannot bulk restore campaign segments', function () {
//         $segments = CampaignSegment::factory()->count(2)->deleted()->create();

//         $this->actingAs($this->normalUser())
//             ->postJson('/campaign-segments/bulk/restore', ['ids' => $segments->pluck('id')->all()])
//             ->assertStatus(403);
//     });
// });

// describe('export', function () {
//     test('authenticated user with permission can export campaign segments as csv', function () {
//         CampaignSegment::factory()->count(2)->create();

//         $response = $this->actingAs($this->superAdminUser())
//             ->get('/campaign-segments/export')
//             ->assertStatus(200);

//         expect($response->headers->get('content-type'))->toContain('text/csv');
//     });

//     test('export contains the segment name and omits criteria', function () {
//         CampaignSegment::factory()->create(['name' => 'Exported Segment Name']);

//         $content = $this->actingAs($this->superAdminUser())
//             ->get('/campaign-segments/export')
//             ->assertStatus(200)
//             ->streamedContent();

//         expect($content)->toContain('Exported Segment Name')
//             ->and($content)->not->toContain('email_subscribed');
//     });

//     test('export neutralises spreadsheet formula injection', function () {
//         CampaignSegment::factory()->create(['name' => '=HYPERLINK("http://example.com")']);

//         $content = $this->actingAs($this->superAdminUser())
//             ->get('/campaign-segments/export')
//             ->assertStatus(200)
//             ->streamedContent();

//         expect($content)->not->toMatch('/^=HYPERLINK/m')
//             ->and($content)->not->toContain(',=HYPERLINK');
//     });

//     test('export writes an audit log entry', function () {
//         $superAdmin = $this->superAdminUser();

//         $this->actingAs($superAdmin)->get('/campaign-segments/export')->streamedContent();

//         expect(
//             Log::query()->where('action_id', Log::ACTION_EXPORT_CAMPAIGN_SEGMENT)->exists()
//         )->toBeTrue();
//     });

//     test('unauthenticated user cannot export campaign segments', function () {
//         $this->get('/campaign-segments/export')->assertRedirect('/login');
//     });

//     test('user without permission cannot export campaign segments', function () {
//         $this->actingAs($this->normalUser())
//             ->get('/campaign-segments/export')
//             ->assertStatus(403);
//     });
// });

// describe('import', function () {
//     test('user without permission cannot preview an import', function () {
//         $this->actingAs($this->normalUser())
//             ->postJson('/campaign-segments/import/preview', [
//                 'file' => UploadedFile::fake()->createWithContent('segments.csv', "name\nTest\n"),
//             ])
//             ->assertStatus(403);
//     });

//     test('preview fails validation when file is missing', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments/import/preview', [])
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['file']);
//     });

//     test('preview fails validation when file is not a csv', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments/import/preview', [
//                 'file' => UploadedFile::fake()->create('segments.pdf', 100, 'application/pdf'),
//             ])
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['file']);
//     });

//     test('preview returns a token without persisting any segments', function () {
//         Storage::fake('local');

//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments/import/preview', [
//                 'file' => UploadedFile::fake()->createWithContent(
//                     'segments.csv',
//                     "name,description\nImported Segment,From a file\n"
//                 ),
//             ])
//             ->assertStatus(200)
//             ->assertJsonStructure(['token', 'valid_count', 'skipped_count'])
//             ->assertJsonPath('valid_count', 1);

//         $this->assertDatabaseMissing('campaign_segments', ['name' => 'Imported Segment']);
//     });

//     test('preview flags a name that already exists in the organisation', function () {
//         Storage::fake('local');
//         CampaignSegment::factory()->create(['name' => 'Existing Segment']);

//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments/import/preview', [
//                 'file' => UploadedFile::fake()->createWithContent('segments.csv', "name\nExisting Segment\n"),
//             ])
//             ->assertStatus(200)
//             ->assertJsonPath('rows.0.valid', false)
//             ->assertJsonPath('rows.0.reason', 'A segment with this name already exists');
//     });

//     test('preview flags a missing name and an over-long name', function () {
//         Storage::fake('local');

//         $csv = "name,description\n,No name\n".str_repeat('a', 256).",Too long\n";

//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments/import/preview', [
//                 'file' => UploadedFile::fake()->createWithContent('segments.csv', $csv),
//             ])
//             ->assertStatus(200)
//             ->assertJsonPath('valid_count', 0)
//             ->assertJsonPath('skipped_count', 2);
//     });

//     test('commit persists valid rows with empty criteria', function () {
//         Storage::fake('local');
//         $superAdmin = $this->superAdminUser();

//         $token = $this->actingAs($superAdmin)
//             ->postJson('/campaign-segments/import/preview', [
//                 'file' => UploadedFile::fake()->createWithContent('segments.csv', "name,description\nCommitted Segment,Via import\n"),
//             ])
//             ->assertStatus(200)
//             ->json('token');

//         $this->actingAs($superAdmin)
//             ->postJson('/campaign-segments/import/commit', ['token' => $token])
//             ->assertStatus(200)
//             ->assertJsonPath('imported', 1);

//         $segment = CampaignSegment::where('name', 'Committed Segment')->firstOrFail();

//         expect($segment->criteria)->toBeNull()
//             ->and($segment->created_by)->toBe($superAdmin->id);

//         expect(
//             Log::query()->where('action_id', Log::ACTION_IMPORT_CAMPAIGN_SEGMENT)->exists()
//         )->toBeTrue();
//     });

//     test('commit skips a duplicate name that appears twice in the same file', function () {
//         Storage::fake('local');
//         $superAdmin = $this->superAdminUser();

//         $token = $this->actingAs($superAdmin)
//             ->postJson('/campaign-segments/import/preview', [
//                 'file' => UploadedFile::fake()->createWithContent('segments.csv', "name\nTwice\nTwice\n"),
//             ])
//             ->json('token');

//         $this->actingAs($superAdmin)
//             ->postJson('/campaign-segments/import/commit', ['token' => $token])
//             ->assertStatus(200)
//             ->assertJsonPath('imported', 1);

//         expect(CampaignSegment::where('name', 'Twice')->count())->toBe(1);
//     });

//     test('a token cannot be committed by a different user', function () {
//         Storage::fake('local');

//         $token = $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments/import/preview', [
//                 'file' => UploadedFile::fake()->createWithContent('segments.csv', "name\nOwned Segment\n"),
//             ])
//             ->json('token');

//         $this->actingAs($this->adminUser())
//             ->postJson('/campaign-segments/import/commit', ['token' => $token])
//             ->assertStatus(422);

//         $this->assertDatabaseMissing('campaign_segments', ['name' => 'Owned Segment']);
//     });

//     test('commit fails validation when token is not a uuid', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments/import/commit', ['token' => 'not-a-uuid'])
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['token']);
//     });

//     test('commit fails validation when token is missing', function () {
//         $this->actingAs($this->superAdminUser())
//             ->postJson('/campaign-segments/import/commit', [])
//             ->assertStatus(422)
//             ->assertJsonValidationErrors(['token']);
//     });

//     test('user without permission cannot commit an import', function () {
//         $this->actingAs($this->normalUser())
//             ->postJson('/campaign-segments/import/commit', ['token' => Str::uuid()->toString()])
//             ->assertStatus(403);
//     });
// });

// describe('soft delete scoping', function () {
//     test('index does not return soft-deleted campaign segments', function () {
//         CampaignSegment::factory()->count(2)->create();
//         $trashed = CampaignSegment::factory()->deleted()->create();

//         $this->actingAs($this->superAdminUser())
//             ->get('/campaign-segments')
//             ->assertStatus(200)
//             ->assertInertia(fn (Assert $page) => $page
//                 ->component('CampaignSegments/Index', false)
//                 ->has('campaignSegments.data', 2)
//             );

//         $this->assertSoftDeleted('campaign_segments', ['id' => $trashed->id]);
//     });

//     test('index can include trashed campaign segments when requested', function () {
//         CampaignSegment::factory()->count(2)->create();
//         CampaignSegment::factory()->deleted()->create();

//         $this->actingAs($this->superAdminUser())
//             ->get('/campaign-segments?trashed=with')
//             ->assertStatus(200)
//             ->assertInertia(fn (Assert $page) => $page
//                 ->component('CampaignSegments/Index', false)
//                 ->has('campaignSegments.data', 3)
//             );
//     });
// });

<?php

use App\Models\Activity;
use App\Models\Company;
use App\Models\Log;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
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
    test('authenticated user with permission can list a company\'s activities', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        Activity::factory()->count(3)->forModel($company)->create();

        $this->actingAs($superAdmin)
            ->getJson('/activities?'.http_build_query([
                'activityable_type' => 'company',
                'activityable_id' => $company->id,
            ]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'activities' => ['data', 'links', 'meta'],
                'permissions_meta',
                'sort_fields',
                'trash_filters',
                'activityableTypes',
            ]);
    });

    test('unauthenticated user cannot list activities', function () {
        $company = Company::factory()->create();

        $this->getJson('/activities?'.http_build_query([
            'activityable_type' => 'company',
            'activityable_id' => $company->id,
        ]))->assertStatus(401);
    });

    test('user without permission cannot list activities', function () {
        $user = $this->userWithNoPermissions();
        $company = Company::factory()->create();

        $this->actingAs($user)
            ->getJson('/activities?'.http_build_query([
                'activityable_type' => 'company',
                'activityable_id' => $company->id,
            ]))
            ->assertStatus(403);
    });

    test('index only returns activities for the requested activityable record', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        Activity::factory()->count(2)->forModel($company)->create();
        Activity::factory()->forModel($otherCompany)->create();

        $response = $this->actingAs($superAdmin)
            ->getJson('/activities?'.http_build_query([
                'activityable_type' => 'company',
                'activityable_id' => $company->id,
            ]))
            ->assertStatus(200);

        expect($response->json('activities.data'))->toHaveCount(2);
    });
});

describe('store', function () {
    test('authenticated user with permission can log an activity', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $payload = [
            'activityable_type' => 'company',
            'activityable_id' => $company->id,
            'type' => 'note',
            'description' => 'Had a great call with the client today.',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/activities', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['type' => 'note']);

        $this->assertDatabaseHas('activities', [
            'activityable_type' => Company::class,
            'activityable_id' => $company->id,
            'type' => 'note',
        ]);
    });

    test('user without permission cannot log an activity', function () {
        $user = $this->normalUser();
        $company = Company::factory()->create();

        $this->actingAs($user)
            ->postJson('/activities', [
                'activityable_type' => 'company',
                'activityable_id' => $company->id,
                'type' => 'note',
                'description' => 'Should not be allowed.',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when activityable_type is missing', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/activities', [
                'activityable_id' => $company->id,
                'type' => 'note',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['activityable_type']);
    });

    test('store fails validation when activityable_id is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/activities', [
                'activityable_type' => 'company',
                'type' => 'note',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['activityable_id']);
    });

    test('store fails validation when type is missing', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/activities', [
                'activityable_type' => 'company',
                'activityable_id' => $company->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    });

    test('store fails validation when type is not a recognised enum value', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/activities', [
                'activityable_type' => 'company',
                'activityable_id' => $company->id,
                'type' => 'not-a-real-type',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    });

    test('store fails validation when activityable_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/activities', [
                'activityable_type' => 'company',
                'activityable_id' => 99999,
                'type' => 'note',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['activityable_id']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/activities', [
                'activityable_type' => 'company',
                'activityable_id' => $company->id,
                'type' => 'status_change',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('activities', [
            'activityable_id' => $company->id,
            'type' => 'status_change',
            'description' => null,
        ]);
    });

    test('logs activity creation with actor id', function () {
        $actor = $this->adminUser();
        $company = Company::factory()->create();

        $this->actingAs($actor)
            ->postJson('/activities', [
                'activityable_type' => 'company',
                'activityable_id' => $company->id,
                'type' => 'note',
                'description' => 'Logged via test.',
            ])
            ->assertCreated();

        $log = Log::query()
            ->where('action_id', Log::ACTION_CREATE_ACTIVITY)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKey('after');
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete an activity', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $activity = Activity::factory()->forModel($company)->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/activities/{$activity->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('activities', ['id' => $activity->id]);
    });

    test('user without permission cannot soft delete an activity', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();
        $company = Company::factory()->create();

        $activity = Activity::factory()->forModel($company)->create();

        $this->actingAs($user)
            ->deleteJson("/activities/{$activity->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for non-existent activity', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/activities/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted activity', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $activity = Activity::factory()->forModel($company)->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/activities/{$activity->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('activities', [
            'id' => $activity->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore an activity', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();
        $company = Company::factory()->create();

        $activity = Activity::factory()->forModel($company)->deleted()->create();

        $this->actingAs($user)
            ->postJson("/activities/{$activity->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for an activity that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $activity = Activity::factory()->forModel($company)->create();

        $this->actingAs($superAdmin)
            ->postJson("/activities/{$activity->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete an activity', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $activity = Activity::factory()->forModel($company)->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/activities/{$activity->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
    });

    test('user without permission cannot force delete an activity', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();
        $company = Company::factory()->create();

        $activity = Activity::factory()->forModel($company)->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/activities/{$activity->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for an activity that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $activity = Activity::factory()->forModel($company)->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/activities/{$activity->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete activities', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $activities = Activity::factory()->count(3)->forModel($company)->create();
        $ids = $activities->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/activities/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('activities', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/activities/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/activities/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete activities', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();
        $company = Company::factory()->create();

        $activities = Activity::factory()->count(2)->forModel($company)->create();

        $this->actingAs($user)
            ->postJson('/activities/bulk/delete', [
                'ids' => $activities->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore activities', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $activities = Activity::factory()->count(3)->forModel($company)->deleted()->create();
        $ids = $activities->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/activities/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('activities', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/activities/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/activities/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore activities', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();
        $company = Company::factory()->create();

        $activities = Activity::factory()->count(2)->forModel($company)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/activities/bulk/restore', [
                'ids' => $activities->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('export', function () {
    test('authenticated user with permission can export a company\'s activities', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        Activity::factory()->count(3)->forModel($company)->create();

        $this->actingAs($superAdmin)
            ->get('/activities/export?'.http_build_query([
                'activityable_type' => 'company',
                'activityable_id' => $company->id,
            ]))
            ->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    });

    test('user without permission cannot export activities', function () {
        $user = $this->normalUser();
        $company = Company::factory()->create();

        $this->actingAs($user)
            ->get('/activities/export?'.http_build_query([
                'activityable_type' => 'company',
                'activityable_id' => $company->id,
            ]))
            ->assertStatus(403);
    });

    test('logs a single audit entry per export', function () {
        $actor = $this->adminUser();
        $company = Company::factory()->create();

        Activity::factory()->count(5)->forModel($company)->create();

        $this->actingAs($actor)
            ->get('/activities/export?'.http_build_query([
                'activityable_type' => 'company',
                'activityable_id' => $company->id,
            ]))
            ->assertOk();

        $count = Log::query()
            ->where('action_id', Log::ACTION_EXPORT_ACTIVITY)
            ->where('logged_in_user_id', $actor->id)
            ->count();

        expect($count)->toBe(1);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted activities by default', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        Activity::factory()->count(2)->forModel($company)->create();
        $trashed = Activity::factory()->forModel($company)->deleted()->create();

        $response = $this->actingAs($superAdmin)
            ->getJson('/activities?'.http_build_query([
                'activityable_type' => 'company',
                'activityable_id' => $company->id,
            ]))
            ->assertStatus(200);

        expect($response->json('activities.data'))->toHaveCount(2);
        $this->assertSoftDeleted('activities', ['id' => $trashed->id]);
    });
});

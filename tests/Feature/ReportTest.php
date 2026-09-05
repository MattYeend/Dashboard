<?php

use App\Models\Log;
use App\Models\Report;
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
    test('authenticated user with permission can list reports', function () {
        $superAdmin = $this->superAdminUser();

        Report::factory()->count(3)->create(['created_by' => $superAdmin->id]);

        $this->actingAs($superAdmin)
            ->get('/reports')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/Index')
                ->has('reports')
            );
    });

    test('unauthenticated user cannot list reports', function () {
        $this->get('/reports')->assertRedirect('/login');
    });

    test('user without permission cannot list reports', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/reports')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/reports/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Reports/Create'));
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/reports/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create an unscheduled report', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/reports', [
                'title' => 'Monthly Orders Summary',
                'type' => 'orders',
                'format' => 'pdf',
                'is_scheduled' => false,
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Monthly Orders Summary']);

        $this->assertDatabaseHas('reports', ['title' => 'Monthly Orders Summary']);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/reports', [
                'type' => 'orders',
                'format' => 'pdf',
                'is_scheduled' => false,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when format is invalid', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/reports', [
                'title' => 'Test Report',
                'type' => 'orders',
                'format' => 'docx',
                'is_scheduled' => false,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['format']);
    });

    test('store fails validation when scheduled without a frequency and time', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/reports', [
                'title' => 'Test Report',
                'type' => 'orders',
                'format' => 'pdf',
                'is_scheduled' => true,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['schedule_frequency', 'schedule_time']);
    });

    test('user without the schedule reports permission cannot create a scheduled report', function () {
        $user = $this->userWithPermissions(['create reports', 'view reports']);

        $this->actingAs($user)
            ->postJson('/reports', [
                'title' => 'Test Report',
                'type' => 'orders',
                'format' => 'pdf',
                'is_scheduled' => true,
                'schedule_frequency' => 'daily',
                'schedule_time' => '06:00',
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('reports', ['title' => 'Test Report']);
    });

    test('user with the schedule reports permission can create a scheduled report', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/reports', [
                'title' => 'Scheduled Report',
                'type' => 'orders',
                'format' => 'csv',
                'is_scheduled' => true,
                'schedule_frequency' => 'daily',
                'schedule_time' => '06:00',
                'recipients' => ['ops@example.com'],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('reports', [
            'title' => 'Scheduled Report',
            'is_scheduled' => true,
            'schedule_frequency' => 'daily',
        ]);
    });

    test('user without permission cannot create a report', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/reports', [
                'title' => 'Test Report',
                'type' => 'orders',
                'format' => 'pdf',
                'is_scheduled' => false,
            ])
            ->assertStatus(403);
    });

    test('logs report creation with actor id', function () {
        $actor = $this->adminUser();

        $this->actingAs($actor)
            ->postJson('/reports', [
                'title' => 'Test Report',
                'type' => 'orders',
                'format' => 'pdf',
                'is_scheduled' => false,
            ])
            ->assertStatus(201);

        $log = Log::query()
            ->where('action_id', Log::ACTION_CREATE_REPORT)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['after']);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a report', function () {
        $superAdmin = $this->superAdminUser();

        $report = Report::factory()->create(['created_by' => $superAdmin->id]);

        $this->actingAs($superAdmin)
            ->get("/reports/{$report->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/Show')
                ->has('report')
            );
    });

    test('show returns 404 for a non-existent report', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/reports/99999')
            ->assertStatus(404);
    });

    test('user without permission cannot view a report', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $report = Report::factory()->create(['created_by' => $superAdmin->id]);

        $this->actingAs($user)
            ->get("/reports/{$report->id}")
            ->assertStatus(403);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $report = Report::factory()->create(['created_by' => $superAdmin->id]);

        $this->actingAs($superAdmin)
            ->get("/reports/{$report->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/Edit')
                ->has('report')
            );
    });
});

describe('update', function () {
    test('authenticated user with permission can update a report', function () {
        $superAdmin = $this->superAdminUser();

        $report = Report::factory()->create(['created_by' => $superAdmin->id, 'title' => 'Old Title']);

        $this->actingAs($superAdmin)
            ->putJson("/reports/{$report->id}", ['title' => 'New Title'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'New Title']);

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'title' => 'New Title']);
    });

    test('patch verb also updates a report', function () {
        $superAdmin = $this->superAdminUser();

        $report = Report::factory()->create(['created_by' => $superAdmin->id, 'title' => 'Old Title']);

        $this->actingAs($superAdmin)
            ->patchJson("/reports/{$report->id}", ['title' => 'Patched Title'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'Patched Title']);
    });

    test('user without permission cannot update a report', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $report = Report::factory()->create(['created_by' => $superAdmin->id]);

        $this->actingAs($user)
            ->putJson("/reports/{$report->id}", ['title' => 'Hacked Title'])
            ->assertStatus(403);
    });

    test('turning on scheduling without the schedule reports permission fails', function () {
        $user = $this->userWithPermissions(['edit reports', 'view reports']);

        $report = Report::factory()->create(['created_by' => $user->id, 'is_scheduled' => false]);

        $this->actingAs($user)
            ->putJson("/reports/{$report->id}", [
                'is_scheduled' => true,
                'schedule_frequency' => 'daily',
                'schedule_time' => '06:00',
            ])
            ->assertStatus(422);

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'is_scheduled' => false]);
    });

    test('logs report updates with actor id', function () {
        $actor = $this->adminUser();

        $report = Report::factory()->create(['created_by' => $actor->id, 'title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/reports/{$report->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_REPORT)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a report', function () {
        $superAdmin = $this->superAdminUser();

        $report = Report::factory()->create(['created_by' => $superAdmin->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/reports/{$report->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('reports', ['id' => $report->id]);
    });

    test('user without permission cannot delete a report', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $report = Report::factory()->create(['created_by' => $superAdmin->id]);

        $this->actingAs($user)
            ->deleteJson("/reports/{$report->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'deleted_at' => null]);
    });

    test('destroy returns 404 for a non-existent report', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/reports/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted report', function () {
        $superAdmin = $this->superAdminUser();

        $report = Report::factory()->deleted()->create(['created_by' => $superAdmin->id]);

        $this->actingAs($superAdmin)
            ->postJson("/reports/{$report->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'deleted_at' => null]);
    });

    test('restore returns 404 for a report that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $report = Report::factory()->create(['created_by' => $superAdmin->id]);

        $this->actingAs($superAdmin)
            ->postJson("/reports/{$report->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a report', function () {
        $superAdmin = $this->superAdminUser();

        $report = Report::factory()->deleted()->create(['created_by' => $superAdmin->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/reports/{$report->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete reports', function () {
        $superAdmin = $this->superAdminUser();

        $reports = Report::factory()->count(3)->create(['created_by' => $superAdmin->id]);
        $ids = $reports->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/reports/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson(['deleted' => $ids, 'skipped' => []]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('reports', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/reports/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore reports', function () {
        $superAdmin = $this->superAdminUser();

        $reports = Report::factory()->count(3)->deleted()->create(['created_by' => $superAdmin->id]);
        $ids = $reports->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/reports/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson(['restored' => $ids, 'skipped' => []]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('reports', ['id' => $id, 'deleted_at' => null]);
        }
    });
});

describe('scheduled command', function () {
    test('reports:run-scheduled processes due reports and rolls next_run_at forward', function () {
        $superAdmin = $this->superAdminUser();

        $due = Report::factory()->due()->create(['created_by' => $superAdmin->id, 'type' => 'orders']);
        $notDue = Report::factory()->scheduled()->create(['created_by' => $superAdmin->id, 'type' => 'orders']);

        $this->artisan('reports:run-scheduled')->assertExitCode(0);

        $due->refresh();
        $notDue->refresh();

        expect($due->last_run_at)->not->toBeNull()
            ->and($due->next_run_at->isFuture())->toBeTrue()
            ->and($notDue->last_run_at)->toBeNull();
    });
});

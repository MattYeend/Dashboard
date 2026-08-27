<?php

use App\Models\Task;
use App\Models\TaskStatus;
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
    test('authenticated user with permission can view the calendar page', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/calendar')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Calendar/Index')
            );
    });

    test('unauthenticated user cannot view the calendar page', function () {
        $this->get('/calendar')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view the calendar page', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/calendar')
            ->assertStatus(403);
    });
});

describe('events', function () {
    test('authenticated user with permission can list tasks within the requested date range', function () {
        $superAdmin = $this->superAdminUser();

        $status = TaskStatus::factory()->create();

        $inRange = Task::factory()->create([
            'due_date' => '2026-09-10',
            'status_id' => $status->id,
        ]);

        $outOfRange = Task::factory()->create([
            'due_date' => '2026-11-10',
            'status_id' => $status->id,
        ]);

        $this->actingAs($superAdmin)
            ->getJson('/calendar/events?start=2026-09-01&end=2026-09-30')
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $inRange->id])
            ->assertJsonMissing(['id' => $outOfRange->id]);
    });

    test('events includes tasks matched by assigned_date as well as due_date', function () {
        $superAdmin = $this->superAdminUser();

        $status = TaskStatus::factory()->create();

        $task = Task::factory()->create([
            'due_date' => null,
            'assigned_date' => '2026-09-15',
            'status_id' => $status->id,
        ]);

        $this->actingAs($superAdmin)
            ->getJson('/calendar/events?start=2026-09-01&end=2026-09-30')
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $task->id]);
    });

    test('unauthenticated user cannot list events', function () {
        $this->getJson('/calendar/events?start=2026-09-01&end=2026-09-30')
            ->assertStatus(401);
    });

    test('user without permission cannot list events', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->getJson('/calendar/events?start=2026-09-01&end=2026-09-30')
            ->assertStatus(403);
    });

    test('events fails validation when start is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->getJson('/calendar/events?end=2026-09-30')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start']);
    });

    test('events fails validation when end is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->getJson('/calendar/events?start=2026-09-01')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end']);
    });

    test('events fails validation when end is before start', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->getJson('/calendar/events?start=2026-09-30&end=2026-09-01')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end']);
    });

    test('events rejects a date range beyond the allowed span', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->getJson('/calendar/events?start=2026-01-01&end=2026-12-31')
            ->assertStatus(422);
    });
});

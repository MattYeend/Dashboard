<?php

use App\Models\Log;
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

test('example', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

describe('index', function () {
    test('authenticated user with permission can list tasks', function () {
        $superAdmin = $this->superAdminUser();

        Task::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/tasks')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tasks/Index')
                ->has('tasks')
            );
    });

    test('unauthenticated user cannot list tasks', function () {
        $this->get('/tasks')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list tasks', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/tasks')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/tasks/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tasks/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/tasks/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/tasks/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a task', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'title' => 'Write unit tests',
            'description' => 'Cover all service classes with Pest tests.',
            'due_date' => '2025-08-01',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/tasks', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Write unit tests']);

        $this->assertDatabaseHas('tasks', ['title' => 'Write unit tests']);
    });

    test('user without permission cannot create a task', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/tasks', ['title' => 'Write unit tests'])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tasks', ['description' => 'No title here'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when due_date is not a valid date', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tasks', [
                'title' => 'Write unit tests',
                'due_date' => 'not-a-date',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    });

    test('store fails validation when assigned_to does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tasks', [
                'title' => 'Write unit tests',
                'assigned_to' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['assigned_to']);
    });

    test('store fails validation when status_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tasks', [
                'title' => 'Write unit tests',
                'status_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status_id']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tasks', ['title' => 'Minimal task'])
            ->assertStatus(201);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Minimal task',
            'description' => null,
        ]);
    });

    test('store succeeds with an assigned user and status', function () {
        $superAdmin = $this->superAdminUser();
        $assignee = $this->normalUser();
        $taskStatus = TaskStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/tasks', [
                'title' => 'Assigned task',
                'assigned_to' => $assignee->id,
                'status_id' => $taskStatus->id,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Assigned task',
            'assigned_to' => $assignee->id,
            'status_id' => $taskStatus->id,
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tasks', [
                'title' => 'Task with meta',
                'meta' => ['priority' => 'high', 'tags' => ['backend']],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('tasks', ['title' => 'Task with meta']);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a task', function () {
        $superAdmin = $this->superAdminUser();
        $task = Task::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/tasks/{$task->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tasks/Show')
                ->has('task')
            );
    });

    test('unauthenticated user cannot view a task', function () {
        $task = Task::factory()->create();

        $this->get("/tasks/{$task->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a task', function () {
        $user = $this->userWithNoPermissions();
        $task = Task::factory()->create();

        $this->actingAs($user)
            ->get("/tasks/{$task->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent task', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/tasks/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();
        $task = Task::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/tasks/{$task->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tasks/Edit')
                ->has('task')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $task = Task::factory()->create();

        $this->get("/tasks/{$task->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();
        $task = Task::factory()->create();

        $this->actingAs($user)
            ->get("/tasks/{$task->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a task', function () {
        $superAdmin = $this->superAdminUser();
        $task = Task::factory()->create(['title' => 'Old title']);

        $this->actingAs($superAdmin)
            ->putJson("/tasks/{$task->id}", ['title' => 'New title'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'New title']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'New title',
        ]);
    });

    test('patch verb also updates a task', function () {
        $superAdmin = $this->superAdminUser();
        $task = Task::factory()->create(['description' => 'Old description']);

        $this->actingAs($superAdmin)
            ->patchJson("/tasks/{$task->id}", ['description' => 'New description'])
            ->assertStatus(200)
            ->assertJsonFragment(['description' => 'New description']);
    });

    test('user without permission cannot update a task', function () {
        $user = $this->normalUser();
        $task = Task::factory()->create();

        $this->actingAs($user)
            ->putJson("/tasks/{$task->id}", ['title' => 'New title'])
            ->assertStatus(403);
    });

    test('update fails validation when due_date is not a valid date', function () {
        $superAdmin = $this->superAdminUser();
        $task = Task::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/tasks/{$task->id}", ['due_date' => 'not-a-date'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    });

    test('update fails validation when assigned_to does not exist', function () {
        $superAdmin = $this->superAdminUser();
        $task = Task::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/tasks/{$task->id}", ['assigned_to' => 99999])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['assigned_to']);
    });

    test('nullable fields can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();
        $task = Task::factory()->create([
            'description' => 'Some description.',
            'due_date' => '2025-08-01',
            'assigned_date' => '2025-07-01',
            'assigned_to' => $this->normalUser()->id,
            'status_id' => TaskStatus::factory()->create()->id,
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/tasks/{$task->id}", [
                'description' => null,
                'due_date' => null,
                'assigned_date' => null,
                'assigned_to' => null,
                'status_id' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'description' => null,
            'due_date' => null,
            'assigned_date' => null,
            'assigned_to' => null,
            'status_id' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();
        $task = Task::factory()->create([
            'description' => 'Original description.',
            'due_date' => '2025-08-01',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/tasks/{$task->id}", [
                'title' => 'Updated title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated title',
            'description' => 'Original description.',
            'due_date' => '2025-08-01 00:00:00',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();
        $task = Task::factory()->create([
            'description' => 'Some description.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/tasks/{$task->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'description' => null,
        ]);
    });

    test('logs task updates with actor id', function () {
        $actor = $this->adminUser();

        $task = Task::factory()->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/tasks/{$task->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_TASK)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a task', function () {
        $superAdmin = $this->superAdminUser();
        $task = Task::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/tasks/{$task->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    });

    test('user without permission cannot soft delete a task', function () {
        $user = $this->normalUser();
        $task = Task::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/tasks/{$task->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent task', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/tasks/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted task', function () {
        $superAdmin = $this->superAdminUser();
        $task = Task::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/tasks/{$task->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a task', function () {
        $user = $this->normalUser();
        $task = Task::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/tasks/{$task->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a task that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $task = Task::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/tasks/{$task->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a task', function () {
        $superAdmin = $this->superAdminUser();
        $task = Task::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/tasks/{$task->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    });

    test('user without permission cannot force delete a task', function () {
        $user = $this->normalUser();
        $task = Task::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/tasks/{$task->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a task that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $task = Task::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/tasks/{$task->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete tasks', function () {
        $superAdmin = $this->superAdminUser();
        $tasks = Task::factory()->count(3)->create();
        $ids = $tasks->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/tasks/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('tasks', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tasks/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tasks/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete tasks', function () {
        $user = $this->normalUser();
        $tasks = Task::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/tasks/bulk/delete', [
                'ids' => $tasks->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore tasks', function () {
        $superAdmin = $this->superAdminUser();
        $tasks = Task::factory()->count(3)->deleted()->create();
        $ids = $tasks->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/tasks/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('tasks', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tasks/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tasks/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore tasks', function () {
        $user = $this->normalUser();
        $tasks = Task::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/tasks/bulk/restore', [
                'ids' => $tasks->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted tasks', function () {
        $superAdmin = $this->superAdminUser();

        Task::factory()->count(2)->create();
        $trashed = Task::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/tasks')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tasks/Index')
                ->has('tasks')
            );

        $this->assertSoftDeleted('tasks', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted task', function () {
        $superAdmin = $this->superAdminUser();
        $task = Task::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/tasks/{$task->id}")
            ->assertStatus(404);
    });
});

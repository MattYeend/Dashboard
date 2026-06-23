<?php

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
    test('authenticated user with permission can list task statuses', function () {
        $superAdmin = $this->superAdminUser();

        TaskStatus::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/task-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TaskStatuses/Index')
                ->has('task_statuses')
            );
    });

    test('unauthenticated user cannot list task statuses', function () {
        $this->get('/task-statuses')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list task statuses', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/task-statuses')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/task-statuses/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TaskStatuses/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/task-statuses/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/task-statuses/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a task status', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'title' => 'In Progress',
            'description' => 'Task is actively being worked on.',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/task-statuses', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'In Progress']);

        $this->assertDatabaseHas('task_statuses', [
            'title' => 'In Progress',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('user without permission cannot create a task status', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/task-statuses', [
                'title' => 'In Progress',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/task-statuses', [
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/task-statuses', [
                'title' => 'In Progress',
                'background_colour' => 'not-a-colour',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('store fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/task-statuses', [
                'title' => 'In Progress',
                'background_colour' => '#bee3f8',
                'text_colour' => 'not-a-colour',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/task-statuses', [
                'title' => 'To Do',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('task_statuses', [
            'title' => 'To Do',
            'description' => null,
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/task-statuses', [
                'title' => 'In Review',
                'meta' => ['order' => 3, 'icon' => 'clock'],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('task_statuses', [
            'title' => 'In Review',
        ]);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a task status', function () {
        $superAdmin = $this->superAdminUser();

        $taskStatus = TaskStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/task-statuses/{$taskStatus->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TaskStatuses/Show')
                ->has('taskStatus')
            );
    });

    test('unauthenticated user cannot view a task status', function () {
        $taskStatus = TaskStatus::factory()->create();

        $this->get("/task-statuses/{$taskStatus->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a task status', function () {
        $user = $this->normalUser();

        $taskStatus = TaskStatus::factory()->create();

        $this->actingAs($user)
            ->get("/task-statuses/{$taskStatus->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent task status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/task-statuses/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $taskStatus = TaskStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/task-statuses/{$taskStatus->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TaskStatuses/Edit')
                ->has('taskStatus')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $taskStatus = TaskStatus::factory()->create();

        $this->get("/task-statuses/{$taskStatus->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $taskStatus = TaskStatus::factory()->create();

        $this->actingAs($user)
            ->get("/task-statuses/{$taskStatus->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a task status', function () {
        $superAdmin = $this->superAdminUser();

        $taskStatus = TaskStatus::factory()->create(['title' => 'To Do']);

        $this->actingAs($superAdmin)
            ->putJson("/task-statuses/{$taskStatus->id}", ['title' => 'In Progress'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'In Progress']);

        $this->assertDatabaseHas('task_statuses', [
            'id' => $taskStatus->id,
            'title' => 'In Progress',
        ]);
    });

    test('patch verb also updates a task status', function () {
        $superAdmin = $this->superAdminUser();

        $taskStatus = TaskStatus::factory()->create(['background_colour' => '#ffffff']);

        $this->actingAs($superAdmin)
            ->patchJson("/task-statuses/{$taskStatus->id}", ['background_colour' => '#bee3f8'])
            ->assertStatus(200)
            ->assertJsonFragment(['background_colour' => '#bee3f8']);
    });

    test('user without permission cannot update a task status', function () {
        $user = $this->normalUser();

        $taskStatus = TaskStatus::factory()->create();

        $this->actingAs($user)
            ->putJson("/task-statuses/{$taskStatus->id}", ['title' => 'In Progress'])
            ->assertStatus(403);
    });

    test('update fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $taskStatus = TaskStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/task-statuses/{$taskStatus->id}", ['background_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('update fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $taskStatus = TaskStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/task-statuses/{$taskStatus->id}", ['text_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a task status', function () {
        $superAdmin = $this->superAdminUser();

        $taskStatus = TaskStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/task-statuses/{$taskStatus->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('task_statuses', ['id' => $taskStatus->id]);
    });

    test('user without permission cannot soft delete a task status', function () {
        $user = $this->normalUser();

        $taskStatus = TaskStatus::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/task-statuses/{$taskStatus->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent task status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/task-statuses/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted task status', function () {
        $superAdmin = $this->superAdminUser();

        $taskStatus = TaskStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/task-statuses/{$taskStatus->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('task_statuses', [
            'id' => $taskStatus->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a task status', function () {
        $user = $this->normalUser();

        $taskStatus = TaskStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/task-statuses/{$taskStatus->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a task status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $taskStatus = TaskStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/task-statuses/{$taskStatus->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a task status', function () {
        $superAdmin = $this->superAdminUser();

        $taskStatus = TaskStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/task-statuses/{$taskStatus->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('task_statuses', ['id' => $taskStatus->id]);
    });

    test('user without permission cannot force delete a task status', function () {
        $user = $this->normalUser();

        $taskStatus = TaskStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/task-statuses/{$taskStatus->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a task status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $taskStatus = TaskStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/task-statuses/{$taskStatus->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete task statuses', function () {
        $superAdmin = $this->superAdminUser();

        $taskStatuses = TaskStatus::factory()->count(3)->create();
        $ids = $taskStatuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/task-statuses/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('task_statuses', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/task-statuses/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/task-statuses/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete task statuses', function () {
        $user = $this->normalUser();

        $taskStatuses = TaskStatus::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/task-statuses/bulk/delete', [
                'ids' => $taskStatuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore task statuses', function () {
        $superAdmin = $this->superAdminUser();

        $taskStatuses = TaskStatus::factory()->count(3)->deleted()->create();
        $ids = $taskStatuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/task-statuses/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('task_statuses', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/task-statuses/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/task-statuses/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore task statuses', function () {
        $user = $this->normalUser();

        $taskStatuses = TaskStatus::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/task-statuses/bulk/restore', [
                'ids' => $taskStatuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted task statuses', function () {
        $superAdmin = $this->superAdminUser();

        TaskStatus::factory()->count(2)->create();
        $trashed = TaskStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/task-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TaskStatuses/Index')
                ->has('task_statuses')
            );

        $this->assertSoftDeleted('task_statuses', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted task status', function () {
        $superAdmin = $this->superAdminUser();

        $taskStatus = TaskStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/task-statuses/{$taskStatus->id}")
            ->assertStatus(404);
    });
});

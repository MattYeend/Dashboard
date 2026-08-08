<?php

use App\Models\Log;
use App\Models\TicketPriority;
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
    test('authenticated user with permission can list ticket priorities', function () {
        $superAdmin = $this->superAdminUser();

        TicketPriority::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/ticket-priorities')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TicketPriorities/Index')
                ->has('ticketPriorities')
            );
    });

    test('unauthenticated user cannot list ticket priorities', function () {
        $this->get('/ticket-priorities')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list ticket priorities', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/ticket-priorities')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/ticket-priorities/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TicketPriorities/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/ticket-priorities/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/ticket-priorities/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a ticket priority', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'title' => 'High',
            'level' => 3,
            'background_colour' => '#feebc8',
            'text_colour' => '#7b341e',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/ticket-priorities', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'High']);

        $this->assertDatabaseHas('ticket_priorities', [
            'title' => 'High',
            'level' => 3,
            'background_colour' => '#feebc8',
            'text_colour' => '#7b341e',
        ]);
    });

    test('user without permission cannot create a ticket priority', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/ticket-priorities', [
                'title' => 'High',
                'background_colour' => '#feebc8',
                'text_colour' => '#7b341e',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-priorities', [
                'background_colour' => '#feebc8',
                'text_colour' => '#7b341e',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when title already exists', function () {
        $superAdmin = $this->superAdminUser();

        TicketPriority::factory()->create(['title' => 'High']);

        $this->actingAs($superAdmin)
            ->postJson('/ticket-priorities', ['title' => 'High'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when level is below the minimum', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-priorities', [
                'title' => 'High',
                'level' => 0,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['level']);
    });

    test('store fails validation when level exceeds the maximum', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-priorities', [
                'title' => 'High',
                'level' => 5,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['level']);
    });

    test('store fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-priorities', [
                'title' => 'High',
                'background_colour' => 'not-a-colour',
                'text_colour' => '#7b341e',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('store fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-priorities', [
                'title' => 'High',
                'background_colour' => '#feebc8',
                'text_colour' => 'not-a-colour',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-priorities', [
                'title' => 'Low',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('ticket_priorities', [
            'title' => 'Low',
            'level' => 1,
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-priorities', [
                'title' => 'Critical',
                'meta' => ['icon' => 'alert'],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('ticket_priorities', [
            'title' => 'Critical',
        ]);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a ticket priority', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/ticket-priorities/{$ticketPriority->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TicketPriorities/Show')
                ->has('ticketPriority')
            );
    });

    test('unauthenticated user cannot view a ticket priority', function () {
        $ticketPriority = TicketPriority::factory()->create();

        $this->get("/ticket-priorities/{$ticketPriority->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a ticket priority', function () {
        $user = $this->userWithNoPermissions();

        $ticketPriority = TicketPriority::factory()->create();

        $this->actingAs($user)
            ->get("/ticket-priorities/{$ticketPriority->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent ticket priority', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/ticket-priorities/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/ticket-priorities/{$ticketPriority->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TicketPriorities/Edit')
                ->has('ticketPriority')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $ticketPriority = TicketPriority::factory()->create();

        $this->get("/ticket-priorities/{$ticketPriority->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $ticketPriority = TicketPriority::factory()->create();

        $this->actingAs($user)
            ->get("/ticket-priorities/{$ticketPriority->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a ticket priority', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->create(['title' => 'Low']);

        $this->actingAs($superAdmin)
            ->putJson("/ticket-priorities/{$ticketPriority->id}", ['title' => 'Medium'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'Medium']);

        $this->assertDatabaseHas('ticket_priorities', [
            'id' => $ticketPriority->id,
            'title' => 'Medium',
        ]);
    });

    test('patch verb also updates a ticket priority', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->create(['background_colour' => '#ffffff']);

        $this->actingAs($superAdmin)
            ->patchJson("/ticket-priorities/{$ticketPriority->id}", ['background_colour' => '#feebc8'])
            ->assertStatus(200)
            ->assertJsonFragment(['background_colour' => '#feebc8']);
    });

    test('user without permission cannot update a ticket priority', function () {
        $user = $this->normalUser();

        $ticketPriority = TicketPriority::factory()->create();

        $this->actingAs($user)
            ->putJson("/ticket-priorities/{$ticketPriority->id}", ['title' => 'Medium'])
            ->assertStatus(403);
    });

    test('update fails validation when title already exists on another record', function () {
        $superAdmin = $this->superAdminUser();

        TicketPriority::factory()->create(['title' => 'High']);
        $ticketPriority = TicketPriority::factory()->create(['title' => 'Low']);

        $this->actingAs($superAdmin)
            ->putJson("/ticket-priorities/{$ticketPriority->id}", ['title' => 'High'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('update succeeds when title is unchanged', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->create(['title' => 'High']);

        $this->actingAs($superAdmin)
            ->putJson("/ticket-priorities/{$ticketPriority->id}", [
                'title' => 'High',
                'level' => 4,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('ticket_priorities', [
            'id' => $ticketPriority->id,
            'title' => 'High',
            'level' => 4,
        ]);
    });

    test('update fails validation when level exceeds the maximum', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/ticket-priorities/{$ticketPriority->id}", ['level' => 5])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['level']);
    });

    test('update fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/ticket-priorities/{$ticketPriority->id}", ['background_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('update fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/ticket-priorities/{$ticketPriority->id}", ['text_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('background_colour and text_colour cannot be nulled and fail validation', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->create([
            'background_colour' => '#feebc8',
            'text_colour' => '#7b341e',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/ticket-priorities/{$ticketPriority->id}", [
                'background_colour' => null,
                'text_colour' => null,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour', 'text_colour']);

        $this->assertDatabaseHas('ticket_priorities', [
            'id' => $ticketPriority->id,
            'background_colour' => '#feebc8',
            'text_colour' => '#7b341e',
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->create([
            'level' => 2,
            'background_colour' => '#feebc8',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/ticket-priorities/{$ticketPriority->id}", [
                'title' => 'Updated Title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('ticket_priorities', [
            'id' => $ticketPriority->id,
            'title' => 'Updated Title',
            'level' => 2,
            'background_colour' => '#feebc8',
        ]);
    });

    test('logs ticket priority updates with actor id', function () {
        $actor = $this->adminUser();

        $ticketPriority = TicketPriority::factory()->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/ticket-priorities/{$ticketPriority->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_TICKET_PRIORITY)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a ticket priority', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/ticket-priorities/{$ticketPriority->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('ticket_priorities', ['id' => $ticketPriority->id]);
    });

    test('user without permission cannot soft delete a ticket priority', function () {
        $user = $this->normalUser();

        $ticketPriority = TicketPriority::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/ticket-priorities/{$ticketPriority->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent ticket priority', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/ticket-priorities/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted ticket priority', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/ticket-priorities/{$ticketPriority->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('ticket_priorities', [
            'id' => $ticketPriority->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a ticket priority', function () {
        $user = $this->normalUser();

        $ticketPriority = TicketPriority::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/ticket-priorities/{$ticketPriority->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a ticket priority that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/ticket-priorities/{$ticketPriority->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a ticket priority', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/ticket-priorities/{$ticketPriority->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('ticket_priorities', ['id' => $ticketPriority->id]);
    });

    test('user without permission cannot force delete a ticket priority', function () {
        $user = $this->normalUser();

        $ticketPriority = TicketPriority::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/ticket-priorities/{$ticketPriority->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a ticket priority that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/ticket-priorities/{$ticketPriority->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete ticket priorities', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriorities = TicketPriority::factory()->count(3)->create();
        $ids = $ticketPriorities->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-priorities/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('ticket_priorities', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-priorities/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-priorities/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete ticket priorities', function () {
        $user = $this->normalUser();

        $ticketPriorities = TicketPriority::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/ticket-priorities/bulk/delete', [
                'ids' => $ticketPriorities->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore ticket priorities', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriorities = TicketPriority::factory()->count(3)->deleted()->create();
        $ids = $ticketPriorities->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-priorities/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('ticket_priorities', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-priorities/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-priorities/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore ticket priorities', function () {
        $user = $this->normalUser();

        $ticketPriorities = TicketPriority::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/ticket-priorities/bulk/restore', [
                'ids' => $ticketPriorities->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted ticket priorities', function () {
        $superAdmin = $this->superAdminUser();

        TicketPriority::factory()->count(2)->create();
        $trashed = TicketPriority::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/ticket-priorities')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TicketPriorities/Index')
                ->has('ticketPriorities')
            );

        $this->assertSoftDeleted('ticket_priorities', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted ticket priority', function () {
        $superAdmin = $this->superAdminUser();

        $ticketPriority = TicketPriority::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/ticket-priorities/{$ticketPriority->id}")
            ->assertStatus(404);
    });
});

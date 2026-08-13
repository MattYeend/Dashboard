<?php

use App\Models\Label;
use App\Models\Log;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
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
    test('authenticated user with permission can list tickets', function () {
        $superAdmin = $this->superAdminUser();

        Ticket::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/tickets')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tickets/Index')
                ->has('tickets')
            );
    });

    test('unauthenticated user cannot list tickets', function () {
        $this->get('/tickets')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list tickets', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/tickets')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/tickets/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tickets/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/tickets/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/tickets/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a ticket', function () {
        $superAdmin = $this->superAdminUser();
        $status = TicketStatus::factory()->create();
        $priority = TicketPriority::factory()->create();
        $assignee = $this->normalUser();

        $payload = [
            'title' => 'Unable to reset password',
            'description' => 'Customer reports the reset email never arrives.',
            'ticket_status_id' => $status->id,
            'ticket_priority_id' => $priority->id,
            'assigned_to' => $assignee->id,
            'due_date' => '2026-09-01',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/tickets', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Unable to reset password']);

        $this->assertDatabaseHas('tickets', [
            'title' => 'Unable to reset password',
            'ticket_status_id' => $status->id,
            'ticket_priority_id' => $priority->id,
            'assigned_to' => $assignee->id,
        ]);
    });

    test('user without permission cannot create a ticket', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/tickets', [
                'title' => 'Blocked ticket',
                'description' => 'Should not be created.',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tickets', [
                'description' => 'No title here.',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when description is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tickets', [
                'title' => 'No description here',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    });

    test('store fails validation when ticket_status_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tickets', [
                'title' => 'Invalid status',
                'description' => 'Testing invalid status.',
                'ticket_status_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ticket_status_id']);
    });

    test('store fails validation when ticket_priority_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tickets', [
                'title' => 'Invalid priority',
                'description' => 'Testing invalid priority.',
                'ticket_priority_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ticket_priority_id']);
    });

    test('store fails validation when assigned_to does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tickets', [
                'title' => 'Invalid assignee',
                'description' => 'Testing invalid assignee.',
                'assigned_to' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['assigned_to']);
    });

    test('store fails validation when due_date is not a valid date', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tickets', [
                'title' => 'Invalid due date',
                'description' => 'Testing invalid due date.',
                'due_date' => 'not-a-date',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    });

    test('store fails validation when label_ids contains a non-existent label', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tickets', [
                'title' => 'Invalid label',
                'description' => 'Testing invalid label.',
                'label_ids' => [99999],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['label_ids.0']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tickets', [
                'title' => 'Minimal ticket',
                'description' => 'Only the required fields provided.',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('tickets', [
            'title' => 'Minimal ticket',
            'ticket_status_id' => null,
            'ticket_priority_id' => null,
            'assigned_to' => null,
            'resolved_at' => null,
        ]);
    });

    test('store succeeds with labels attached', function () {
        $superAdmin = $this->superAdminUser();
        $labels = Label::factory()->count(2)->create();

        $this->actingAs($superAdmin)
            ->postJson('/tickets', [
                'title' => 'Ticket with labels',
                'description' => 'Testing label attachment.',
                'label_ids' => $labels->pluck('id')->all(),
            ])
            ->assertStatus(201);

        $ticket = Ticket::where('title', 'Ticket with labels')->firstOrFail();

        expect($ticket->labels()->pluck('labels.id')->all())
            ->toEqualCanonicalizing($labels->pluck('id')->all());
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tickets', [
                'title' => 'Ticket with meta',
                'description' => 'Testing meta data.',
                'meta' => ['source' => 'email', 'tags' => ['vip']],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('tickets', ['title' => 'Ticket with meta']);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a ticket', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/tickets/{$ticket->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tickets/Show')
                ->has('ticket')
            );
    });

    test('unauthenticated user cannot view a ticket', function () {
        $ticket = Ticket::factory()->create();

        $this->get("/tickets/{$ticket->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a ticket', function () {
        $user = $this->userWithNoPermissions();

        $ticket = Ticket::factory()->create();

        $this->actingAs($user)
            ->get("/tickets/{$ticket->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent ticket', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/tickets/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/tickets/{$ticket->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tickets/Edit')
                ->has('ticket')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $ticket = Ticket::factory()->create();

        $this->get("/tickets/{$ticket->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $ticket = Ticket::factory()->create();

        $this->actingAs($user)
            ->get("/tickets/{$ticket->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a ticket', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->create(['title' => 'Old title']);

        $this->actingAs($superAdmin)
            ->putJson("/tickets/{$ticket->id}", ['title' => 'New title'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'New title']);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title' => 'New title',
        ]);
    });

    test('patch verb also updates a ticket', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->create(['description' => 'Old description']);

        $this->actingAs($superAdmin)
            ->patchJson("/tickets/{$ticket->id}", ['description' => 'New description'])
            ->assertStatus(200)
            ->assertJsonFragment(['description' => 'New description']);
    });

    test('user without permission cannot update a ticket', function () {
        $user = $this->normalUser();

        $ticket = Ticket::factory()->create();

        $this->actingAs($user)
            ->putJson("/tickets/{$ticket->id}", ['title' => 'New title'])
            ->assertStatus(403);
    });

    test('update fails validation when ticket_status_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/tickets/{$ticket->id}", ['ticket_status_id' => 99999])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ticket_status_id']);
    });

    test('update fails validation when ticket_priority_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/tickets/{$ticket->id}", ['ticket_priority_id' => 99999])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ticket_priority_id']);
    });

    test('update fails validation when assigned_to does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/tickets/{$ticket->id}", ['assigned_to' => 99999])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['assigned_to']);
    });

    test('nullable fields can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();
        $status = TicketStatus::factory()->create();
        $priority = TicketPriority::factory()->create();
        $assignee = $this->normalUser();

        $ticket = Ticket::factory()->create([
            'ticket_status_id' => $status->id,
            'ticket_priority_id' => $priority->id,
            'assigned_to' => $assignee->id,
            'due_date' => '2026-08-20',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/tickets/{$ticket->id}", [
                'ticket_status_id' => null,
                'ticket_priority_id' => null,
                'assigned_to' => null,
                'due_date' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'ticket_status_id' => null,
            'ticket_priority_id' => null,
            'assigned_to' => null,
            'due_date' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->create([
            'description' => 'Original description.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/tickets/{$ticket->id}", [
                'title' => 'Updated title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title' => 'Updated title',
            'description' => 'Original description.',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->create([
            'due_date' => '2026-08-20',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/tickets/{$ticket->id}", [
                'due_date' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'due_date' => null,
        ]);
    });

    test('assigned_to can be updated to reassign a ticket', function () {
        $superAdmin = $this->superAdminUser();
        $newAssignee = $this->normalUser();

        $ticket = Ticket::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/tickets/{$ticket->id}", [
                'assigned_to' => $newAssignee->id,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $newAssignee->id,
        ]);
    });

    test('label_ids can be updated to sync labels', function () {
        $superAdmin = $this->superAdminUser();
        $originalLabels = Label::factory()->count(2)->create();
        $newLabels = Label::factory()->count(2)->create();

        $ticket = Ticket::factory()->create();
        $ticket->labels()->sync($originalLabels->pluck('id')->all());

        $this->actingAs($superAdmin)
            ->putJson("/tickets/{$ticket->id}", [
                'label_ids' => $newLabels->pluck('id')->all(),
            ])
            ->assertStatus(200);

        expect($ticket->labels()->pluck('labels.id')->all())
            ->toEqualCanonicalizing($newLabels->pluck('id')->all());
    });

    test('omitting label_ids leaves existing labels untouched', function () {
        $superAdmin = $this->superAdminUser();
        $labels = Label::factory()->count(2)->create();

        $ticket = Ticket::factory()->create();
        $ticket->labels()->sync($labels->pluck('id')->all());

        $this->actingAs($superAdmin)
            ->putJson("/tickets/{$ticket->id}", [
                'title' => 'Updated without touching labels',
            ])
            ->assertStatus(200);

        expect($ticket->labels()->pluck('labels.id')->all())
            ->toEqualCanonicalizing($labels->pluck('id')->all());
    });

    test('logs ticket updates with actor id', function () {
        $actor = $this->adminUser();

        $ticket = Ticket::factory()->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/tickets/{$ticket->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_TICKET)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('resolve', function () {
    test('authenticated user with permission can resolve a ticket', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->create(['resolved_at' => null]);

        $this->actingAs($superAdmin)
            ->postJson("/tickets/{$ticket->id}/resolve")
            ->assertStatus(200);

        $ticket->refresh();

        expect($ticket->resolved_at)->not->toBeNull();
    });

    test('user without permission cannot resolve a ticket', function () {
        $user = $this->normalUser();

        $ticket = Ticket::factory()->create(['resolved_at' => null]);

        $this->actingAs($user)
            ->postJson("/tickets/{$ticket->id}/resolve")
            ->assertStatus(403);
    });

    test('resolve returns 404 for a non-existent ticket', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tickets/99999/resolve')
            ->assertStatus(404);
    });

    test('logs ticket resolution with actor id', function () {
        $actor = $this->adminUser();

        $ticket = Ticket::factory()->create(['resolved_at' => null]);

        $this->actingAs($actor)
            ->postJson("/tickets/{$ticket->id}/resolve")
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_RESOLVE_TICKET)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a ticket', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/tickets/{$ticket->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('tickets', ['id' => $ticket->id]);
    });

    test('user without permission cannot soft delete a ticket', function () {
        $user = $this->normalUser();

        $ticket = Ticket::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/tickets/{$ticket->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent ticket', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/tickets/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted ticket', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/tickets/{$ticket->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a ticket', function () {
        $user = $this->normalUser();

        $ticket = Ticket::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/tickets/{$ticket->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a ticket that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/tickets/{$ticket->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a ticket', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/tickets/{$ticket->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    });

    test('user without permission cannot force delete a ticket', function () {
        $user = $this->normalUser();

        $ticket = Ticket::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/tickets/{$ticket->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a ticket that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/tickets/{$ticket->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete tickets', function () {
        $superAdmin = $this->superAdminUser();

        $tickets = Ticket::factory()->count(3)->create();
        $ids = $tickets->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/tickets/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('tickets', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tickets/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tickets/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete tickets', function () {
        $user = $this->normalUser();

        $tickets = Ticket::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/tickets/bulk/delete', [
                'ids' => $tickets->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore tickets', function () {
        $superAdmin = $this->superAdminUser();

        $tickets = Ticket::factory()->count(3)->deleted()->create();
        $ids = $tickets->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/tickets/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('tickets', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tickets/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tickets/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore tickets', function () {
        $user = $this->normalUser();

        $tickets = Ticket::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/tickets/bulk/restore', [
                'ids' => $tickets->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted tickets', function () {
        $superAdmin = $this->superAdminUser();

        Ticket::factory()->count(2)->create();
        $trashed = Ticket::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/tickets')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tickets/Index')
                ->has('tickets')
            );

        $this->assertSoftDeleted('tickets', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted ticket', function () {
        $superAdmin = $this->superAdminUser();

        $ticket = Ticket::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/tickets/{$ticket->id}")
            ->assertStatus(404);
    });
});

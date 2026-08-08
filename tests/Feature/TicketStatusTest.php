<?php

use App\Models\Log;
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
    test('authenticated user with permission can list ticket statuses', function () {
        $superAdmin = $this->superAdminUser();

        TicketStatus::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/ticket-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TicketStatuses/Index')
                ->has('ticketStatuses')
            );
    });

    test('unauthenticated user cannot list ticket statuses', function () {
        $this->get('/ticket-statuses')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list ticket statuses', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/ticket-statuses')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/ticket-statuses/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TicketStatuses/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/ticket-statuses/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/ticket-statuses/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a ticket status', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'title' => 'In Progress',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/ticket-statuses', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'In Progress']);

        $this->assertDatabaseHas('ticket_statuses', [
            'title' => 'In Progress',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('user without permission cannot create a ticket status', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/ticket-statuses', [
                'title' => 'In Progress',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-statuses', [
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-statuses', [
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
            ->postJson('/ticket-statuses', [
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
            ->postJson('/ticket-statuses', [
                'title' => 'Open',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('ticket_statuses', [
            'title' => 'Open',
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-statuses', [
                'title' => 'In Review',
                'meta' => ['order' => 3, 'icon' => 'clock'],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('ticket_statuses', [
            'title' => 'In Review',
        ]);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a ticket status', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatus = TicketStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/ticket-statuses/{$ticketStatus->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TicketStatuses/Show')
                ->has('ticketStatus')
            );
    });

    test('unauthenticated user cannot view a ticket status', function () {
        $ticketStatus = TicketStatus::factory()->create();

        $this->get("/ticket-statuses/{$ticketStatus->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a ticket status', function () {
        $user = $this->userWithNoPermissions();

        $ticketStatus = TicketStatus::factory()->create();

        $this->actingAs($user)
            ->get("/ticket-statuses/{$ticketStatus->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent ticket status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/ticket-statuses/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatus = TicketStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/ticket-statuses/{$ticketStatus->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TicketStatuses/Edit')
                ->has('ticketStatus')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $ticketStatus = TicketStatus::factory()->create();

        $this->get("/ticket-statuses/{$ticketStatus->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $ticketStatus = TicketStatus::factory()->create();

        $this->actingAs($user)
            ->get("/ticket-statuses/{$ticketStatus->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a ticket status', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatus = TicketStatus::factory()->create(['title' => 'Open']);

        $this->actingAs($superAdmin)
            ->putJson("/ticket-statuses/{$ticketStatus->id}", ['title' => 'In Progress'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'In Progress']);

        $this->assertDatabaseHas('ticket_statuses', [
            'id' => $ticketStatus->id,
            'title' => 'In Progress',
        ]);
    });

    test('patch verb also updates a ticket status', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatus = TicketStatus::factory()->create(['background_colour' => '#ffffff']);

        $this->actingAs($superAdmin)
            ->patchJson("/ticket-statuses/{$ticketStatus->id}", ['background_colour' => '#bee3f8'])
            ->assertStatus(200)
            ->assertJsonFragment(['background_colour' => '#bee3f8']);
    });

    test('user without permission cannot update a ticket status', function () {
        $user = $this->normalUser();

        $ticketStatus = TicketStatus::factory()->create();

        $this->actingAs($user)
            ->putJson("/ticket-statuses/{$ticketStatus->id}", ['title' => 'In Progress'])
            ->assertStatus(403);
    });

    test('update fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatus = TicketStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/ticket-statuses/{$ticketStatus->id}", ['background_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('update fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatus = TicketStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/ticket-statuses/{$ticketStatus->id}", ['text_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('background_colour and text_colour cannot be nulled and fail validation', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatus = TicketStatus::factory()->create([
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/ticket-statuses/{$ticketStatus->id}", [
                'background_colour' => null,
                'text_colour' => null,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour', 'text_colour']);

        $this->assertDatabaseHas('ticket_statuses', [
            'id' => $ticketStatus->id,
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatus = TicketStatus::factory()->create([
            'background_colour' => '#bee3f8',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/ticket-statuses/{$ticketStatus->id}", [
                'title' => 'Updated Title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('ticket_statuses', [
            'id' => $ticketStatus->id,
            'title' => 'Updated Title',
            'background_colour' => '#bee3f8',
        ]);
    });

    test('logs ticket status updates with actor id', function () {
        $actor = $this->adminUser();

        $ticketStatus = TicketStatus::factory()->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/ticket-statuses/{$ticketStatus->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_TICKET_STATUS)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a ticket status', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatus = TicketStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/ticket-statuses/{$ticketStatus->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('ticket_statuses', ['id' => $ticketStatus->id]);
    });

    test('user without permission cannot soft delete a ticket status', function () {
        $user = $this->normalUser();

        $ticketStatus = TicketStatus::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/ticket-statuses/{$ticketStatus->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent ticket status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/ticket-statuses/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted ticket status', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatus = TicketStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/ticket-statuses/{$ticketStatus->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('ticket_statuses', [
            'id' => $ticketStatus->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a ticket status', function () {
        $user = $this->normalUser();

        $ticketStatus = TicketStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/ticket-statuses/{$ticketStatus->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a ticket status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatus = TicketStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/ticket-statuses/{$ticketStatus->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a ticket status', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatus = TicketStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/ticket-statuses/{$ticketStatus->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('ticket_statuses', ['id' => $ticketStatus->id]);
    });

    test('user without permission cannot force delete a ticket status', function () {
        $user = $this->normalUser();

        $ticketStatus = TicketStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/ticket-statuses/{$ticketStatus->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a ticket status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatus = TicketStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/ticket-statuses/{$ticketStatus->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete ticket statuses', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatuses = TicketStatus::factory()->count(3)->create();
        $ids = $ticketStatuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-statuses/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('ticket_statuses', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-statuses/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-statuses/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete ticket statuses', function () {
        $user = $this->normalUser();

        $ticketStatuses = TicketStatus::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/ticket-statuses/bulk/delete', [
                'ids' => $ticketStatuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore ticket statuses', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatuses = TicketStatus::factory()->count(3)->deleted()->create();
        $ids = $ticketStatuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-statuses/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('ticket_statuses', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-statuses/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/ticket-statuses/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore ticket statuses', function () {
        $user = $this->normalUser();

        $ticketStatuses = TicketStatus::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/ticket-statuses/bulk/restore', [
                'ids' => $ticketStatuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted ticket statuses', function () {
        $superAdmin = $this->superAdminUser();

        TicketStatus::factory()->count(2)->create();
        $trashed = TicketStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/ticket-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('TicketStatuses/Index')
                ->has('ticketStatuses')
            );

        $this->assertSoftDeleted('ticket_statuses', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted ticket status', function () {
        $superAdmin = $this->superAdminUser();

        $ticketStatus = TicketStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/ticket-statuses/{$ticketStatus->id}")
            ->assertStatus(404);
    });
});

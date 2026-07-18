<?php

use App\Models\InvoiceStatus;
use App\Models\Log;
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
    test('authenticated user with permission can list invoice statuses', function () {
        $superAdmin = $this->superAdminUser();

        InvoiceStatus::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/invoice-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('InvoiceStatuses/Index')
                ->has('invoiceStatuses')
            );
    });

    test('unauthenticated user cannot list invoice statuses', function () {
        $this->get('/invoice-statuses')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list invoice statuses', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/invoice-statuses')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/invoice-statuses/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('InvoiceStatuses/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/invoice-statuses/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/invoice-statuses/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create an invoice status', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'title' => 'Paid',
            'description' => 'Invoice has been paid in full.',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/invoice-statuses', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Paid']);

        $this->assertDatabaseHas('invoice_statuses', [
            'title' => 'Paid',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('user without permission cannot create an invoice status', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/invoice-statuses', [
                'title' => 'Paid',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoice-statuses', [
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoice-statuses', [
                'title' => 'Paid',
                'background_colour' => 'not-a-colour',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('store fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoice-statuses', [
                'title' => 'Paid',
                'background_colour' => '#bee3f8',
                'text_colour' => 'not-a-colour',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoice-statuses', [
                'title' => 'Pending',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('invoice_statuses', [
            'title' => 'Pending',
            'description' => null,
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoice-statuses', [
                'title' => 'Overdue',
                'meta' => ['order' => 3, 'icon' => 'alert'],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('invoice_statuses', [
            'title' => 'Overdue',
        ]);
    });
});

describe('show', function () {
    test('authenticated user with permission can view an invoice status', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/invoice-statuses/{$invoiceStatus->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('InvoiceStatuses/Show')
                ->has('invoiceStatus')
            );
    });

    test('unauthenticated user cannot view an invoice status', function () {
        $invoiceStatus = InvoiceStatus::factory()->create();

        $this->get("/invoice-statuses/{$invoiceStatus->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view an invoice status', function () {
        $user = $this->normalUser();

        $invoiceStatus = InvoiceStatus::factory()->create();

        $this->actingAs($user)
            ->get("/invoice-statuses/{$invoiceStatus->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent invoice status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/invoice-statuses/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/invoice-statuses/{$invoiceStatus->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('InvoiceStatuses/Edit')
                ->has('invoiceStatus')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $invoiceStatus = InvoiceStatus::factory()->create();

        $this->get("/invoice-statuses/{$invoiceStatus->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $invoiceStatus = InvoiceStatus::factory()->create();

        $this->actingAs($user)
            ->get("/invoice-statuses/{$invoiceStatus->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update an invoice status', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->create(['title' => 'Pending']);

        $this->actingAs($superAdmin)
            ->putJson("/invoice-statuses/{$invoiceStatus->id}", ['title' => 'Paid'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'Paid']);

        $this->assertDatabaseHas('invoice_statuses', [
            'id' => $invoiceStatus->id,
            'title' => 'Paid',
        ]);
    });

    test('patch verb also updates an invoice status', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->create(['background_colour' => '#ffffff']);

        $this->actingAs($superAdmin)
            ->patchJson("/invoice-statuses/{$invoiceStatus->id}", ['background_colour' => '#bee3f8'])
            ->assertStatus(200)
            ->assertJsonFragment(['background_colour' => '#bee3f8']);
    });

    test('user without permission cannot update an invoice status', function () {
        $user = $this->normalUser();

        $invoiceStatus = InvoiceStatus::factory()->create();

        $this->actingAs($user)
            ->putJson("/invoice-statuses/{$invoiceStatus->id}", ['title' => 'Paid'])
            ->assertStatus(403);
    });

    test('update fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/invoice-statuses/{$invoiceStatus->id}", ['background_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('update fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/invoice-statuses/{$invoiceStatus->id}", ['text_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('description can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->create([
            'description' => 'Invoice has been paid in full.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/invoice-statuses/{$invoiceStatus->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('invoice_statuses', [
            'id' => $invoiceStatus->id,
            'description' => null,
        ]);
    });

    test('background_colour and text_colour cannot be nulled and fail validation', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->create([
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/invoice-statuses/{$invoiceStatus->id}", [
                'background_colour' => null,
                'text_colour' => null,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour', 'text_colour']);

        $this->assertDatabaseHas('invoice_statuses', [
            'id' => $invoiceStatus->id,
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->create([
            'description' => 'Original description.',
            'background_colour' => '#bee3f8',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/invoice-statuses/{$invoiceStatus->id}", [
                'title' => 'Updated Title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('invoice_statuses', [
            'id' => $invoiceStatus->id,
            'title' => 'Updated Title',
            'description' => 'Original description.',
            'background_colour' => '#bee3f8',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->create([
            'description' => 'Some description.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/invoice-statuses/{$invoiceStatus->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('invoice_statuses', [
            'id' => $invoiceStatus->id,
            'description' => null,
        ]);
    });

    test('logs invoice status updates with actor id', function () {
        $actor = $this->adminUser();

        $invoiceStatus = InvoiceStatus::factory()->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/invoice-statuses/{$invoiceStatus->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_INVOICE_STATUS)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete an invoice status', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/invoice-statuses/{$invoiceStatus->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('invoice_statuses', ['id' => $invoiceStatus->id]);
    });

    test('user without permission cannot soft delete an invoice status', function () {
        $user = $this->normalUser();

        $invoiceStatus = InvoiceStatus::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/invoice-statuses/{$invoiceStatus->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent invoice status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/invoice-statuses/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted invoice status', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoice-statuses/{$invoiceStatus->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('invoice_statuses', [
            'id' => $invoiceStatus->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore an invoice status', function () {
        $user = $this->normalUser();

        $invoiceStatus = InvoiceStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/invoice-statuses/{$invoiceStatus->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for an invoice status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoice-statuses/{$invoiceStatus->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete an invoice status', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/invoice-statuses/{$invoiceStatus->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('invoice_statuses', ['id' => $invoiceStatus->id]);
    });

    test('user without permission cannot force delete an invoice status', function () {
        $user = $this->normalUser();

        $invoiceStatus = InvoiceStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/invoice-statuses/{$invoiceStatus->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for an invoice status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/invoice-statuses/{$invoiceStatus->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete invoice statuses', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatuses = InvoiceStatus::factory()->count(3)->create();
        $ids = $invoiceStatuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/invoice-statuses/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('invoice_statuses', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoice-statuses/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoice-statuses/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete invoice statuses', function () {
        $user = $this->normalUser();

        $invoiceStatuses = InvoiceStatus::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/invoice-statuses/bulk/delete', [
                'ids' => $invoiceStatuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore invoice statuses', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatuses = InvoiceStatus::factory()->count(3)->deleted()->create();
        $ids = $invoiceStatuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/invoice-statuses/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('invoice_statuses', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoice-statuses/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoice-statuses/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore invoice statuses', function () {
        $user = $this->normalUser();

        $invoiceStatuses = InvoiceStatus::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/invoice-statuses/bulk/restore', [
                'ids' => $invoiceStatuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted invoice statuses', function () {
        $superAdmin = $this->superAdminUser();

        InvoiceStatus::factory()->count(2)->create();
        $trashed = InvoiceStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/invoice-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('InvoiceStatuses/Index')
                ->has('invoiceStatuses')
            );

        $this->assertSoftDeleted('invoice_statuses', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted invoice status', function () {
        $superAdmin = $this->superAdminUser();

        $invoiceStatus = InvoiceStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/invoice-statuses/{$invoiceStatus->id}")
            ->assertStatus(404);
    });
});

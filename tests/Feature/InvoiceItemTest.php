<?php

use App\Models\Invoice;
use App\Models\InvoiceItem;
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

test('example', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

describe('index', function () {
    test('authenticated user with permission can list invoice items', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        InvoiceItem::factory()->count(3)->create(['invoice_id' => $invoice->id]);

        $this->actingAs($superAdmin)
            ->get("/invoices/{$invoice->id}/items")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('InvoiceItems/Index')
                ->has('invoice_items')
            );
    });

    test('unauthenticated user cannot list invoice items', function () {
        $invoice = Invoice::factory()->create();

        $this->get("/invoices/{$invoice->id}/items")
            ->assertRedirect('/login');
    });

    test('user without permission cannot list invoice items', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($user)
            ->get("/invoices/{$invoice->id}/items")
            ->assertStatus(403);
    });

    test('index returns 404 for a non-existent invoice', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/invoices/99999/items')
            ->assertStatus(404);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/invoices/{$invoice->id}/items/create")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('InvoiceItems/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $invoice = Invoice::factory()->create();

        $this->get("/invoices/{$invoice->id}/items/create")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($user)
            ->get("/invoices/{$invoice->id}/items/create")
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create an invoice item', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $payload = [
            'description' => 'Bespoke cabinetry, fitted',
            'quantity' => 2,
            'unit_price' => 15000,
            'tax_rate' => 20,
            'position' => 1,
        ];

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items", $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['description' => 'Bespoke cabinetry, fitted']);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Bespoke cabinetry, fitted',
            'quantity' => 2,
            'unit_price' => 15000,
        ]);
    });

    test('user without permission cannot create an invoice item', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($user)
            ->postJson("/invoices/{$invoice->id}/items", [
                'description' => 'Blocked item',
                'quantity' => 1,
                'unit_price' => 1000,
            ])
            ->assertStatus(403);
    });

    test('store fails validation when description is missing', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items", [
                'quantity' => 1,
                'unit_price' => 1000,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    });

    test('store defaults quantity to one when omitted', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items", [
                'description' => 'Missing quantity item',
                'unit_price' => 1000,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Missing quantity item',
            'quantity' => 1,
        ]);
    });

    test('store fails validation when quantity is less than one', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items", [
                'description' => 'Zero quantity item',
                'quantity' => 0,
                'unit_price' => 1000,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    });

    test('store fails validation when unit_price is negative', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items", [
                'description' => 'Negative price item',
                'quantity' => 1,
                'unit_price' => -500,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['unit_price']);
    });

    test('store fails validation when tax_rate is greater than 100', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items", [
                'description' => 'Invalid tax rate item',
                'quantity' => 1,
                'unit_price' => 1000,
                'tax_rate' => 150,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tax_rate']);
    });

    test('store fails validation when tax_rate is negative', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items", [
                'description' => 'Invalid tax rate item',
                'quantity' => 1,
                'unit_price' => 1000,
                'tax_rate' => -1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tax_rate']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items", [
                'description' => 'Minimal item',
                'quantity' => 1,
                'unit_price' => 500,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Minimal item',
            'quantity' => 1,
            'unit_price' => 500,
            'tax_rate' => 0,
        ]);
    });

    test('store computes the total server-side and ignores a client-supplied total', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items", [
                'description' => 'Total override attempt',
                'quantity' => 2,
                'unit_price' => 1000,
                'total' => 999999,
            ])
            ->assertStatus(201)
            ->assertJsonMissing(['total' => 999999]);
    });

    test('store returns 404 when the invoice does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoices/99999/items', [
                'description' => 'Orphaned item',
                'quantity' => 1,
                'unit_price' => 500,
            ])
            ->assertStatus(404);
    });
});

describe('show', function () {
    test('authenticated user with permission can view an invoice item', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($superAdmin)
            ->get("/invoices/{$invoice->id}/items/{$item->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('InvoiceItems/Show')
                ->has('item')
            );
    });

    test('unauthenticated user cannot view an invoice item', function () {
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->get("/invoices/{$invoice->id}/items/{$item->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view an invoice item', function () {
        $user = $this->userWithNoPermissions();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($user)
            ->get("/invoices/{$invoice->id}/items/{$item->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent invoice item', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/invoices/{$invoice->id}/items/99999")
            ->assertStatus(404);
    });

    test('show returns 404 when the item belongs to a different invoice', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $otherInvoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $otherInvoice->id]);

        $this->actingAs($superAdmin)
            ->get("/invoices/{$invoice->id}/items/{$item->id}")
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($superAdmin)
            ->get("/invoices/{$invoice->id}/items/{$item->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('InvoiceItems/Edit')
                ->has('item')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->get("/invoices/{$invoice->id}/items/{$item->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($user)
            ->get("/invoices/{$invoice->id}/items/{$item->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update an invoice item', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Old description',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/invoices/{$invoice->id}/items/{$item->id}", ['description' => 'New description'])
            ->assertStatus(200)
            ->assertJsonFragment(['description' => 'New description']);

        $this->assertDatabaseHas('invoice_items', [
            'id' => $item->id,
            'description' => 'New description',
        ]);
    });

    test('patch verb also updates an invoice item', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 1,
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/invoices/{$invoice->id}/items/{$item->id}", ['quantity' => 5])
            ->assertStatus(200)
            ->assertJsonFragment(['quantity' => 5]);
    });

    test('user without permission cannot update an invoice item', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($user)
            ->putJson("/invoices/{$invoice->id}/items/{$item->id}", ['description' => 'Blocked update'])
            ->assertStatus(403);
    });

    test('update fails validation when quantity is less than one', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($superAdmin)
            ->putJson("/invoices/{$invoice->id}/items/{$item->id}", ['quantity' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    });

    test('update fails validation when unit_price is negative', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($superAdmin)
            ->putJson("/invoices/{$invoice->id}/items/{$item->id}", ['unit_price' => -100])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['unit_price']);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Original description',
            'quantity' => 3,
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/invoices/{$invoice->id}/items/{$item->id}", ['unit_price' => 750])
            ->assertStatus(200);

        $this->assertDatabaseHas('invoice_items', [
            'id' => $item->id,
            'description' => 'Original description',
            'quantity' => 3,
            'unit_price' => 750,
        ]);
    });

    test('recalculates total when quantity or unit_price changes', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 1,
            'unit_price' => 1000,
        ]);

        $originalTotal = $item->total;

        $this->actingAs($superAdmin)
            ->putJson("/invoices/{$invoice->id}/items/{$item->id}", ['quantity' => 4])
            ->assertStatus(200);

        $item->refresh();

        expect($item->total)->not->toBe($originalTotal);
    });

    test('update returns 404 when the item belongs to a different invoice', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $otherInvoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $otherInvoice->id]);

        $this->actingAs($superAdmin)
            ->putJson("/invoices/{$invoice->id}/items/{$item->id}", ['description' => 'Should not apply'])
            ->assertStatus(404);
    });

    test('logs invoice item updates with actor id', function () {
        $actor = $this->adminUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Old description',
        ]);

        $this->actingAs($actor)
            ->putJson("/invoices/{$invoice->id}/items/{$item->id}", ['description' => 'New description'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_INVOICE_ITEM)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete an invoice item', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/invoices/{$invoice->id}/items/{$item->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('invoice_items', ['id' => $item->id]);
    });

    test('user without permission cannot soft delete an invoice item', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($user)
            ->deleteJson("/invoices/{$invoice->id}/items/{$item->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent invoice item', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/invoices/{$invoice->id}/items/99999")
            ->assertStatus(404);
    });

    test('destroy returns 404 when the item belongs to a different invoice', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $otherInvoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $otherInvoice->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/invoices/{$invoice->id}/items/{$item->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('invoice_items', [
            'id' => $item->id,
            'deleted_at' => null,
        ]);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted invoice item', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->deleted()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items/{$item->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('invoice_items', [
            'id' => $item->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore an invoice item', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->deleted()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($user)
            ->postJson("/invoices/{$invoice->id}/items/{$item->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for an invoice item that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items/{$item->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete an invoice item', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->deleted()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/invoices/{$invoice->id}/items/{$item->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('invoice_items', ['id' => $item->id]);
    });

    test('user without permission cannot force delete an invoice item', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->deleted()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($user)
            ->deleteJson("/invoices/{$invoice->id}/items/{$item->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for an invoice item that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/invoices/{$invoice->id}/items/{$item->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete invoice items', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $items = InvoiceItem::factory()->count(3)->create(['invoice_id' => $invoice->id]);
        $ids = $items->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items/bulk/delete", ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('invoice_items', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items/bulk/delete", ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items/bulk/delete", ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('bulk delete returns 404 when an id belongs to a different invoice', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $otherInvoice = Invoice::factory()->create();
        $foreignItem = InvoiceItem::factory()->create(['invoice_id' => $otherInvoice->id]);

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items/bulk/delete", ['ids' => [$foreignItem->id]])
            ->assertStatus(404);

        $this->assertDatabaseHas('invoice_items', [
            'id' => $foreignItem->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot bulk delete invoice items', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->create();
        $items = InvoiceItem::factory()->count(2)->create(['invoice_id' => $invoice->id]);

        $this->actingAs($user)
            ->postJson("/invoices/{$invoice->id}/items/bulk/delete", [
                'ids' => $items->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore invoice items', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $items = InvoiceItem::factory()->count(3)->deleted()->create(['invoice_id' => $invoice->id]);
        $ids = $items->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items/bulk/restore", ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('invoice_items', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items/bulk/restore", ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items/bulk/restore", ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('bulk restore silently skips an id belonging to a different invoice', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $otherInvoice = Invoice::factory()->create();
        $foreignItem = InvoiceItem::factory()->deleted()->create(['invoice_id' => $otherInvoice->id]);

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/items/bulk/restore", ['ids' => [$foreignItem->id]])
            ->assertStatus(204);

        $this->assertSoftDeleted('invoice_items', ['id' => $foreignItem->id]);
    });

    test('user without permission cannot bulk restore invoice items', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->create();
        $items = InvoiceItem::factory()->count(2)->deleted()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($user)
            ->postJson("/invoices/{$invoice->id}/items/bulk/restore", [
                'ids' => $items->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted invoice items', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        InvoiceItem::factory()->count(2)->create(['invoice_id' => $invoice->id]);
        $trashed = InvoiceItem::factory()->deleted()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($superAdmin)
            ->get("/invoices/{$invoice->id}/items")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('InvoiceItems/Index')
                ->has('invoice_items')
            );

        $this->assertSoftDeleted('invoice_items', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted invoice item', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->deleted()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($superAdmin)
            ->get("/invoices/{$invoice->id}/items/{$item->id}")
            ->assertStatus(404);
    });
});

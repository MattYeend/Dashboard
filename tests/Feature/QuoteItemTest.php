<?php

declare(strict_types=1);

use App\Models\Log;
use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

describe('index', function () {
    test('authenticated user with permission can list quote items', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        QuoteItem::factory()->count(3)->create(['quote_id' => $quote->id]);

        $this->actingAs($superAdmin)
            ->get("/quotes/{$quote->id}/items")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('QuoteItems/Index')
                ->has('quote_items')
            );
    });

    test('unauthenticated user cannot list quote items', function () {
        $quote = Quote::factory()->create();

        $this->get("/quotes/{$quote->id}/items")
            ->assertRedirect('/login');
    });

    test('user without permission cannot list quote items', function () {
        $user = $this->normalUser();
        $quote = Quote::factory()->create();

        $this->actingAs($user)
            ->get("/quotes/{$quote->id}/items")
            ->assertStatus(403);
    });

    test('index returns 404 for a non-existent quote', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/quotes/99999/items')
            ->assertStatus(404);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/quotes/{$quote->id}/items/create")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('QuoteItems/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $quote = Quote::factory()->create();

        $this->get("/quotes/{$quote->id}/items/create")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();
        $quote = Quote::factory()->create();

        $this->actingAs($user)
            ->get("/quotes/{$quote->id}/items/create")
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create an quote item', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        $payload = [
            'name' => 'Bespoke cabinetry',
            'description' => 'Bespoke cabinetry, fitted',
            'quantity' => 2,
            'unit_price' => 15000,
            'tax_rate' => 20,
            'position' => 1,
        ];

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items", $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['description' => 'Bespoke cabinetry, fitted']);

        $this->assertDatabaseHas('quote_items', [
            'quote_id' => $quote->id,
            'description' => 'Bespoke cabinetry, fitted',
            'quantity' => 2,
            'unit_price' => 15000,
        ]);
    });

    test('user without permission cannot create an quote item', function () {
        $user = $this->normalUser();
        $quote = Quote::factory()->create();

        $this->actingAs($user)
            ->postJson("/quotes/{$quote->id}/items", [
                'description' => 'Blocked item',
                'quantity' => 1,
                'unit_price' => 1000,
            ])
            ->assertStatus(403);
    });

    test('store fails validation when name and product are both missing', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items", [
                'quantity' => 1,
                'unit_price' => 1000,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('store fails validation when quantity is omitted', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items", [
                'name' => 'Missing quantity item',
                'unit_price' => 1000,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    });

    test('store fails validation when quantity is less than one', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items", [
                'description' => 'Zero quantity item',
                'quantity' => 0,
                'unit_price' => 1000,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    });

    test('store fails validation when unit_price is negative', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items", [
                'description' => 'Negative price item',
                'quantity' => 1,
                'unit_price' => -500,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['unit_price']);
    });

    test('store fails validation when tax_rate is greater than 100', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items", [
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
        $quote = Quote::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items", [
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
        $quote = Quote::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items", [
                'name' => 'Minimal item',
                'quantity' => 1,
                'unit_price' => 500,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('quote_items', [
            'quote_id' => $quote->id,
            'name' => 'Minimal item',
            'quantity' => 1,
            'unit_price' => 500,
            'tax_rate' => 20,
        ]);
    });

    test('store computes the total server-side and ignores a client-supplied total', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items", [
                'name' => 'Total override attempt',
                'quantity' => 2,
                'unit_price' => 1000,
                'line_total' => 999999,
                'line_tax' => 999999,
            ])
            ->assertStatus(201)
            ->assertJsonMissing(['line_total' => '999999.00']);

        $this->assertDatabaseHas('quote_items', [
            'quote_id' => $quote->id,
            'name' => 'Total override attempt',
            'line_total' => 2000.00,
            'line_tax' => 400.00,
        ]);
    });

    test('store returns 404 when the quote does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quotes/99999/items', [
                'description' => 'Orphaned item',
                'quantity' => 1,
                'unit_price' => 500,
            ])
            ->assertStatus(404);
    });
});

describe('show', function () {
    test('authenticated user with permission can view an quote item', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $quote->id]);

        $this->actingAs($superAdmin)
            ->get("/quotes/{$quote->id}/items/{$item->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('QuoteItems/Show')
                ->has('item')
            );
    });

    test('unauthenticated user cannot view an quote item', function () {
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $quote->id]);

        $this->get("/quotes/{$quote->id}/items/{$item->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view an quote item', function () {
        $user = $this->userWithNoPermissions();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $quote->id]);

        $this->actingAs($user)
            ->get("/quotes/{$quote->id}/items/{$item->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent quote item', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/quotes/{$quote->id}/items/99999")
            ->assertStatus(404);
    });

    test('show returns 404 when the item belongs to a different quote', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $otherInvoice = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $otherInvoice->id]);

        $this->actingAs($superAdmin)
            ->get("/quotes/{$quote->id}/items/{$item->id}")
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $quote->id]);

        $this->actingAs($superAdmin)
            ->get("/quotes/{$quote->id}/items/{$item->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('QuoteItems/Edit')
                ->has('item')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $quote->id]);

        $this->get("/quotes/{$quote->id}/items/{$item->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $quote->id]);

        $this->actingAs($user)
            ->get("/quotes/{$quote->id}/items/{$item->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update an quote item', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create([
            'quote_id' => $quote->id,
            'description' => 'Old description',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$quote->id}/items/{$item->id}", ['description' => 'New description'])
            ->assertStatus(200)
            ->assertJsonFragment(['description' => 'New description']);

        $this->assertDatabaseHas('quote_items', [
            'id' => $item->id,
            'description' => 'New description',
        ]);
    });

    test('patch verb also updates an quote item', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create([
            'quote_id' => $quote->id,
            'quantity' => 1,
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/quotes/{$quote->id}/items/{$item->id}", ['quantity' => 5])
            ->assertStatus(200)
            ->assertJsonFragment(['quantity' => '5.00']);
    });

    test('recalculates line totals when quantity or unit_price changes', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create([
            'quote_id' => $quote->id,
            'quantity' => 1,
            'unit_price' => 1000,
            'discount_percent' => 0,
            'tax_rate' => 20,
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$quote->id}/items/{$item->id}", ['quantity' => 4])
            ->assertStatus(200);

        $item->refresh();

        expect($item->line_total)->toBe('4000.00')
            ->and($item->line_tax)->toBe('800.00');
    });

    test('user without permission cannot update an quote item', function () {
        $user = $this->normalUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $quote->id]);

        $this->actingAs($user)
            ->putJson("/quotes/{$quote->id}/items/{$item->id}", ['description' => 'Blocked update'])
            ->assertStatus(403);
    });

    test('update fails validation when quantity is less than one', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $quote->id]);

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$quote->id}/items/{$item->id}", ['quantity' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    });

    test('update fails validation when unit_price is negative', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $quote->id]);

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$quote->id}/items/{$item->id}", ['unit_price' => -100])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['unit_price']);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create([
            'quote_id' => $quote->id,
            'description' => 'Original description',
            'quantity' => 3,
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$quote->id}/items/{$item->id}", ['unit_price' => 750])
            ->assertStatus(200);

        $this->assertDatabaseHas('quote_items', [
            'id' => $item->id,
            'description' => 'Original description',
            'quantity' => 3,
            'unit_price' => 750,
        ]);
    });

    test('update returns 404 when the item belongs to a different quote', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $otherInvoice = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $otherInvoice->id]);

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$quote->id}/items/{$item->id}", ['description' => 'Should not apply'])
            ->assertStatus(404);
    });

    test('logs quote item updates with actor id', function () {
        $actor = $this->adminUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create([
            'quote_id' => $quote->id,
            'description' => 'Old description',
        ]);

        $this->actingAs($actor)
            ->putJson("/quotes/{$quote->id}/items/{$item->id}", ['description' => 'New description'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_QUOTE_ITEM)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete an quote item', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $quote->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/quotes/{$quote->id}/items/{$item->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('quote_items', ['id' => $item->id]);
    });

    test('user without permission cannot soft delete an quote item', function () {
        $user = $this->normalUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $quote->id]);

        $this->actingAs($user)
            ->deleteJson("/quotes/{$quote->id}/items/{$item->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent quote item', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/quotes/{$quote->id}/items/99999")
            ->assertStatus(404);
    });

    test('destroy returns 404 when the item belongs to a different quote', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $otherInvoice = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $otherInvoice->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/quotes/{$quote->id}/items/{$item->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('quote_items', [
            'id' => $item->id,
            'deleted_at' => null,
        ]);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted quote item', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->deleted()->create(['quote_id' => $quote->id]);

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items/{$item->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('quote_items', [
            'id' => $item->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore an quote item', function () {
        $user = $this->normalUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->deleted()->create(['quote_id' => $quote->id]);

        $this->actingAs($user)
            ->postJson("/quotes/{$quote->id}/items/{$item->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for an quote item that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $quote->id]);

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items/{$item->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete an quote item', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->deleted()->create(['quote_id' => $quote->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/quotes/{$quote->id}/items/{$item->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('quote_items', ['id' => $item->id]);
    });

    test('user without permission cannot force delete an quote item', function () {
        $user = $this->normalUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->deleted()->create(['quote_id' => $quote->id]);

        $this->actingAs($user)
            ->deleteJson("/quotes/{$quote->id}/items/{$item->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for an quote item that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->create(['quote_id' => $quote->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/quotes/{$quote->id}/items/{$item->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete quote items', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $items = QuoteItem::factory()->count(3)->create(['quote_id' => $quote->id]);
        $ids = $items->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items/bulk/delete", ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('quote_items', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items/bulk/delete", ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items/bulk/delete", ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('bulk delete skips ids belonging to a different quote', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $otherInvoice = Quote::factory()->create();
        $foreignItem = QuoteItem::factory()->create(['quote_id' => $otherInvoice->id]);

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items/bulk/delete", ['ids' => [$foreignItem->id]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [$foreignItem->id],
            ]);

        $this->assertDatabaseHas('quote_items', [
            'id' => $foreignItem->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot bulk delete quote items', function () {
        $user = $this->normalUser();
        $quote = Quote::factory()->create();
        $items = QuoteItem::factory()->count(2)->create(['quote_id' => $quote->id]);

        $this->actingAs($user)
            ->postJson("/quotes/{$quote->id}/items/bulk/delete", [
                'ids' => $items->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore quote items', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $items = QuoteItem::factory()->count(3)->deleted()->create(['quote_id' => $quote->id]);
        $ids = $items->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items/bulk/restore", ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('quote_items', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items/bulk/restore", ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items/bulk/restore", ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('bulk restore skips ids belonging to a different quote', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $otherInvoice = Quote::factory()->create();
        $foreignItem = QuoteItem::factory()->deleted()->create(['quote_id' => $otherInvoice->id]);

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/items/bulk/restore", ['ids' => [$foreignItem->id]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [$foreignItem->id],
            ]);

        $this->assertSoftDeleted('quote_items', ['id' => $foreignItem->id]);
    });

    test('user without permission cannot bulk restore quote items', function () {
        $user = $this->normalUser();
        $quote = Quote::factory()->create();
        $items = QuoteItem::factory()->count(2)->deleted()->create(['quote_id' => $quote->id]);

        $this->actingAs($user)
            ->postJson("/quotes/{$quote->id}/items/bulk/restore", [
                'ids' => $items->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted quote items', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();

        QuoteItem::factory()->count(2)->create(['quote_id' => $quote->id]);
        $trashed = QuoteItem::factory()->deleted()->create(['quote_id' => $quote->id]);

        $this->actingAs($superAdmin)
            ->get("/quotes/{$quote->id}/items")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('QuoteItems/Index')
                ->has('quote_items')
            );

        $this->assertSoftDeleted('quote_items', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted quote item', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->create();
        $item = QuoteItem::factory()->deleted()->create(['quote_id' => $quote->id]);

        $this->actingAs($superAdmin)
            ->get("/quotes/{$quote->id}/items/{$item->id}")
            ->assertStatus(404);
    });
});

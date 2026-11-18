<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Log;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\QuoteStatus;
use App\Services\QuoteItems\CreatorService;
use App\Services\Quotes\TotalsService;
use App\Services\Quotes\TransitionService;
use Database\Seeders\QuoteStatusSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesQuotes;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesQuotes::class,
    CreatesUsers::class,
);

beforeEach(function () {
    $this->seed(QuoteStatusSeeder::class);
    /** @var CreatesQuotes&\Tests\TestCase $this */
    $this->quote = $this->draftQuote();
});

describe('index', function () {
    test('authenticated user with permission can list quote items', function () {
        $superAdmin = $this->superAdminUser();

        QuoteItem::factory()->count(3)->create(['quote_id' => $this->quote->id]);

        $this->actingAs($superAdmin)
            ->get("/quotes/{$this->quote->id}/items")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('QuoteItems/Index')
                ->has('quote_items')
            );
    });

    test('unauthenticated user cannot list quote items', function () {
        $this->get("/quotes/{$this->quote->id}/items")
            ->assertRedirect('/login');
    });

    test('user without permission cannot list quote items', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get("/quotes/{$this->quote->id}/items")
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

        $this->actingAs($superAdmin)
            ->get("/quotes/{$this->quote->id}/items/create")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('QuoteItems/Create')
                ->has('products')
                ->has('units')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get("/quotes/{$this->quote->id}/items/create")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get("/quotes/{$this->quote->id}/items/create")
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a quote item', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'name' => 'Bespoke cabinetry, fitted',
                'quantity' => '2',
                'unit_price' => '150.00',
                'tax_rate' => '20',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Bespoke cabinetry, fitted']);

        $this->assertDatabaseHas('quote_items', [
            'quote_id' => $this->quote->id,
            'name' => 'Bespoke cabinetry, fitted',
        ]);
    });

    test('user without permission cannot create a quote item', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'name' => 'Blocked item',
                'quantity' => '1',
                'unit_price' => '1.00',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when name is missing and there is no product', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'quantity' => '1',
                'unit_price' => '1.00',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('store fails validation when quantity is zero', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'name' => 'Zero quantity item',
                'quantity' => '0',
                'unit_price' => '1.00',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    });

    test('store fails validation when unit_price is negative', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'name' => 'Negative price item',
                'quantity' => '1',
                'unit_price' => '-5.00',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['unit_price']);
    });

    test('store fails validation when tax_rate is greater than 100', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'name' => 'Invalid tax rate item',
                'quantity' => '1',
                'unit_price' => '10.00',
                'tax_rate' => '150',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tax_rate']);
    });

    test('store fails validation when discount_percent is greater than 100', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'name' => 'Invalid discount item',
                'quantity' => '1',
                'unit_price' => '10.00',
                'discount_percent' => '101',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['discount_percent']);
    });

    test('store computes the line and recalculates the quote header with bcmath', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'name' => 'Rounded line',
                'quantity' => '3',
                'unit_price' => '19.99',
                'discount_percent' => '10',
                'tax_rate' => '20',
            ])
            ->assertStatus(201);

        $item = QuoteItem::where('quote_id', $this->quote->id)->firstOrFail();
        $quote = $this->quote->fresh();

        expect($item->line_total)->toBe('53.97')
            ->and($item->line_tax)->toBe('10.79')
            ->and($quote->subtotal)->toBe('53.97')
            ->and($quote->tax_amount)->toBe('10.79')
            ->and($quote->total_amount)->toBe('64.76');
    });

    test('store ignores a client-supplied line total, tax, position and quote id', function () {
        $superAdmin = $this->superAdminUser();
        $other = Quote::factory()
            ->forModel(Company::factory()->create())
            ->withStatus(QuoteStatus::KEY_DRAFT)
            ->create();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'name' => 'Hostile',
                'quantity' => '1',
                'unit_price' => '10.00',
                'line_total' => '0.01',
                'line_tax' => '0.00',
                'position' => 99,
                'quote_id' => $other->id,
            ])
            ->assertStatus(201);

        $item = QuoteItem::where('name', 'Hostile')->firstOrFail();

        expect($item->line_total)->toBe('10.00')
            ->and($item->position)->toBe(1)
            ->and($item->quote_id)->toBe($this->quote->id);
    });

    test('store snapshots product details so later price changes do not alter the line', function () {
        $superAdmin = $this->superAdminUser();
        $product = Product::factory()->create([
            'name' => 'Widget',
            'unit_price' => '25.00',
            'currency' => 'GBP',
        ]);

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'product_id' => $product->id,
                'quantity' => '2',
            ])
            ->assertStatus(201);

        $product->update(['unit_price' => '99.00']);

        $item = QuoteItem::where('quote_id', $this->quote->id)->firstOrFail();

        expect($item->name)->toBe('Widget')
            ->and($item->unit_price)->toBe('25.00')
            ->and($item->line_total)->toBe('50.00');
    });

    test('store rejects an inactive product', function () {
        $superAdmin = $this->superAdminUser();
        $product = Product::factory()->inactive()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'product_id' => $product->id,
                'quantity' => '1',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    });

    test('store rejects a product priced in a different currency', function () {
        $superAdmin = $this->superAdminUser();
        $product = Product::factory()->create(['currency' => 'USD']);

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'product_id' => $product->id,
                'quantity' => '1',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    });

    test('store stops at the maximum number of lines', function () {
        $superAdmin = $this->superAdminUser();

        QuoteItem::factory()
            ->count(QuoteItem::MAX_PER_QUOTE)
            ->create(['quote_id' => $this->quote->id]);

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'name' => 'One too many',
                'quantity' => '1',
                'unit_price' => '1.00',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    });

    test('store returns 404 when the quote does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quotes/99999/items', [
                'name' => 'Orphaned item',
                'quantity' => '1',
                'unit_price' => '1.00',
            ])
            ->assertStatus(404);
    });

    test('store is forbidden once the quote is sent', function () {
        $admin = $this->adminUser();

        QuoteItem::factory()->create(['quote_id' => $this->quote->id]);
        DB::transaction(fn () => app(TotalsService::class)->recalculate($this->quote->fresh()));
        app(TransitionService::class)->send($this->quote->fresh(), $admin);

        $this->actingAs($admin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'name' => 'Late',
                'quantity' => '1',
                'unit_price' => '1.00',
            ])
            ->assertStatus(403);
    });

    test('creator service blocks a sent quote even for the Super Admin bypass', function () {
        $superAdmin = $this->superAdminUser();

        QuoteItem::factory()->create(['quote_id' => $this->quote->id]);
        DB::transaction(fn () => app(TotalsService::class)->recalculate($this->quote->fresh()));
        app(TransitionService::class)->send($this->quote->fresh(), $superAdmin);

        app(CreatorService::class)->create(
            $this->quote->fresh(),
            ['name' => 'Late', 'quantity' => '1', 'unit_price' => '1.00'],
            $superAdmin->id,
        );
    })->throws(AuthorizationException::class);
});

describe('show', function () {
    test('authenticated user with permission can view a quote item', function () {
        $superAdmin = $this->superAdminUser();
        $item = QuoteItem::factory()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($superAdmin)
            ->get("/quotes/{$this->quote->id}/items/{$item->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('QuoteItems/Show')
                ->has('item')
            );
    });

    test('unauthenticated user cannot view a quote item', function () {
        $item = QuoteItem::factory()->create(['quote_id' => $this->quote->id]);

        $this->get("/quotes/{$this->quote->id}/items/{$item->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a quote item', function () {
        $user = $this->userWithNoPermissions();
        $item = QuoteItem::factory()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($user)
            ->get("/quotes/{$this->quote->id}/items/{$item->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent quote item', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get("/quotes/{$this->quote->id}/items/99999")
            ->assertStatus(404);
    });

    test('show returns 404 when the item belongs to a different quote', function () {
        $superAdmin = $this->superAdminUser();
        $other = Quote::factory()
            ->forModel(Company::factory()->create())
            ->withStatus(QuoteStatus::KEY_DRAFT)
            ->create();
        $item = QuoteItem::factory()->create(['quote_id' => $other->id]);

        $this->actingAs($superAdmin)
            ->get("/quotes/{$this->quote->id}/items/{$item->id}")
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();
        $item = QuoteItem::factory()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($superAdmin)
            ->get("/quotes/{$this->quote->id}/items/{$item->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('QuoteItems/Edit')
                ->has('item')
                ->has('products')
                ->has('units')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $item = QuoteItem::factory()->create(['quote_id' => $this->quote->id]);

        $this->get("/quotes/{$this->quote->id}/items/{$item->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();
        $item = QuoteItem::factory()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($user)
            ->get("/quotes/{$this->quote->id}/items/{$item->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a quote item', function () {
        $superAdmin = $this->superAdminUser();
        $item = QuoteItem::factory()->create([
            'quote_id' => $this->quote->id,
            'name' => 'Old name',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$this->quote->id}/items/{$item->id}", ['name' => 'New name'])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'New name']);

        $this->assertDatabaseHas('quote_items', [
            'id' => $item->id,
            'name' => 'New name',
        ]);
    });

    test('patch verb also updates a quote item', function () {
        $superAdmin = $this->superAdminUser();
        $item = QuoteItem::factory()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($superAdmin)
            ->patchJson("/quotes/{$this->quote->id}/items/{$item->id}", ['quantity' => '5'])
            ->assertStatus(200);
    });

    test('user without permission cannot update a quote item', function () {
        $user = $this->normalUser();
        $item = QuoteItem::factory()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($user)
            ->putJson("/quotes/{$this->quote->id}/items/{$item->id}", ['name' => 'Blocked update'])
            ->assertStatus(403);
    });

    test('update fails validation when quantity is zero', function () {
        $superAdmin = $this->superAdminUser();
        $item = QuoteItem::factory()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$this->quote->id}/items/{$item->id}", ['quantity' => '0'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    });

    test('update fails validation when unit_price is negative', function () {
        $superAdmin = $this->superAdminUser();
        $item = QuoteItem::factory()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$this->quote->id}/items/{$item->id}", ['unit_price' => '-1.00'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['unit_price']);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();
        $item = QuoteItem::factory()->create([
            'quote_id' => $this->quote->id,
            'name' => 'Original name',
            'quantity' => '3.00',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$this->quote->id}/items/{$item->id}", ['unit_price' => '7.50'])
            ->assertStatus(200);

        $this->assertDatabaseHas('quote_items', [
            'id' => $item->id,
            'name' => 'Original name',
            'quantity' => '3.00',
            'unit_price' => '7.50',
        ]);
    });

    test('update recalculates the line and the quote header', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'name' => 'Line',
                'quantity' => '1',
                'unit_price' => '100.00',
                'tax_rate' => '20',
            ])
            ->assertStatus(201);

        $item = QuoteItem::where('quote_id', $this->quote->id)->firstOrFail();

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$this->quote->id}/items/{$item->id}", ['quantity' => '2'])
            ->assertStatus(200);

        $quote = $this->quote->fresh();

        expect($quote->subtotal)->toBe('200.00')
            ->and($quote->tax_amount)->toBe('40.00')
            ->and($quote->total_amount)->toBe('240.00');
    });

    test('update reduces a header discount that exceeds the new subtotal', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'name' => 'Line',
                'quantity' => '1',
                'unit_price' => '100.00',
                'tax_rate' => '0',
            ])
            ->assertStatus(201);

        $this->quote->forceFill(['discount_amount' => '80.00'])->save();
        $item = QuoteItem::where('quote_id', $this->quote->id)->firstOrFail();

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$this->quote->id}/items/{$item->id}", ['unit_price' => '50.00'])
            ->assertStatus(200);

        expect($this->quote->fresh()->discount_amount)->toBe('50.00')
            ->and($this->quote->fresh()->total_amount)->toBe('0.00');
    });

    test('update returns 404 when the item belongs to a different quote', function () {
        $superAdmin = $this->superAdminUser();
        $other = Quote::factory()
            ->forModel(Company::factory()->create())
            ->withStatus(QuoteStatus::KEY_DRAFT)
            ->create();
        $item = QuoteItem::factory()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$other->id}/items/{$item->id}", ['name' => 'Cross-quote'])
            ->assertStatus(404);
    });

    test('logs quote item updates with actor id', function () {
        $actor = $this->adminUser();
        $item = QuoteItem::factory()->create([
            'quote_id' => $this->quote->id,
            'name' => 'Old name',
        ]);

        $this->actingAs($actor)
            ->putJson("/quotes/{$this->quote->id}/items/{$item->id}", ['name' => 'New name'])
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
    test('authenticated user with permission can soft delete a quote item', function () {
        $superAdmin = $this->superAdminUser();
        $item = QuoteItem::factory()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/quotes/{$this->quote->id}/items/{$item->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('quote_items', ['id' => $item->id]);
    });

    test('user without permission cannot soft delete a quote item', function () {
        $user = $this->normalUser();
        $item = QuoteItem::factory()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($user)
            ->deleteJson("/quotes/{$this->quote->id}/items/{$item->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent quote item', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson("/quotes/{$this->quote->id}/items/99999")
            ->assertStatus(404);
    });

    test('destroy returns 404 when the item belongs to a different quote', function () {
        $superAdmin = $this->superAdminUser();
        $other = Quote::factory()
            ->forModel(Company::factory()->create())
            ->withStatus(QuoteStatus::KEY_DRAFT)
            ->create();
        $item = QuoteItem::factory()->create(['quote_id' => $other->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/quotes/{$this->quote->id}/items/{$item->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('quote_items', [
            'id' => $item->id,
            'deleted_at' => null,
        ]);
    });

    test('destroy keeps the quote header in step', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'name' => 'Line',
                'quantity' => '1',
                'unit_price' => '50.00',
                'tax_rate' => '0',
            ])
            ->assertStatus(201);

        $item = QuoteItem::where('quote_id', $this->quote->id)->firstOrFail();

        $this->actingAs($superAdmin)
            ->deleteJson("/quotes/{$this->quote->id}/items/{$item->id}")
            ->assertStatus(204);

        expect($this->quote->fresh()->total_amount)->toBe('0.00');
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted quote item', function () {
        $superAdmin = $this->superAdminUser();
        $item = QuoteItem::factory()->deleted()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items/{$item->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('quote_items', [
            'id' => $item->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a quote item', function () {
        $user = $this->normalUser();
        $item = QuoteItem::factory()->deleted()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($user)
            ->postJson("/quotes/{$this->quote->id}/items/{$item->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a quote item that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $item = QuoteItem::factory()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items/{$item->id}/restore")
            ->assertStatus(404);
    });

    test('restore keeps the quote header in step', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items", [
                'name' => 'Line',
                'quantity' => '1',
                'unit_price' => '50.00',
                'tax_rate' => '0',
            ])
            ->assertStatus(201);

        $item = QuoteItem::where('quote_id', $this->quote->id)->firstOrFail();

        $this->actingAs($superAdmin)
            ->deleteJson("/quotes/{$this->quote->id}/items/{$item->id}")
            ->assertStatus(204);

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items/{$item->id}/restore")
            ->assertStatus(204);

        expect($this->quote->fresh()->total_amount)->toBe('50.00');
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a quote item', function () {
        $superAdmin = $this->superAdminUser();
        $item = QuoteItem::factory()->deleted()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/quotes/{$this->quote->id}/items/{$item->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('quote_items', ['id' => $item->id]);
    });

    test('user without permission cannot force delete a quote item', function () {
        $user = $this->normalUser();
        $item = QuoteItem::factory()->deleted()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($user)
            ->deleteJson("/quotes/{$this->quote->id}/items/{$item->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a quote item that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $item = QuoteItem::factory()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($superAdmin)
            ->deleteJson("/quotes/{$this->quote->id}/items/{$item->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete quote items', function () {
        $superAdmin = $this->superAdminUser();
        $items = QuoteItem::factory()->count(3)->create(['quote_id' => $this->quote->id]);
        $ids = $items->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items/bulk/delete", ['ids' => $ids])
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

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items/bulk/delete", ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items/bulk/delete", ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('bulk delete skips ids belonging to a different quote', function () {
        $superAdmin = $this->superAdminUser();
        $other = Quote::factory()
            ->forModel(Company::factory()->create())
            ->withStatus(QuoteStatus::KEY_DRAFT)
            ->create();
        $foreignItem = QuoteItem::factory()->create(['quote_id' => $other->id]);

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items/bulk/delete", ['ids' => [$foreignItem->id]])
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
        $items = QuoteItem::factory()->count(2)->create(['quote_id' => $this->quote->id]);

        $this->actingAs($user)
            ->postJson("/quotes/{$this->quote->id}/items/bulk/delete", [
                'ids' => $items->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore quote items', function () {
        $superAdmin = $this->superAdminUser();
        $items = QuoteItem::factory()->count(3)->deleted()->create(['quote_id' => $this->quote->id]);
        $ids = $items->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items/bulk/restore", ['ids' => $ids])
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

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items/bulk/restore", ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items/bulk/restore", ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('bulk restore skips ids belonging to a different quote', function () {
        $superAdmin = $this->superAdminUser();
        $other = Quote::factory()
            ->forModel(Company::factory()->create())
            ->withStatus(QuoteStatus::KEY_DRAFT)
            ->create();
        $foreignItem = QuoteItem::factory()->deleted()->create(['quote_id' => $other->id]);

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items/bulk/restore", ['ids' => [$foreignItem->id]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [$foreignItem->id],
            ]);

        $this->assertSoftDeleted('quote_items', ['id' => $foreignItem->id]);
    });

    test('user without permission cannot bulk restore quote items', function () {
        $user = $this->normalUser();
        $items = QuoteItem::factory()->count(2)->deleted()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($user)
            ->postJson("/quotes/{$this->quote->id}/items/bulk/restore", [
                'ids' => $items->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted quote items', function () {
        $superAdmin = $this->superAdminUser();

        QuoteItem::factory()->count(2)->create(['quote_id' => $this->quote->id]);
        $trashed = QuoteItem::factory()->deleted()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($superAdmin)
            ->get("/quotes/{$this->quote->id}/items")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('QuoteItems/Index')
                ->has('quote_items.data', 2)
            );

        $this->assertSoftDeleted('quote_items', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted quote item', function () {
        $superAdmin = $this->superAdminUser();
        $item = QuoteItem::factory()->deleted()->create(['quote_id' => $this->quote->id]);

        $this->actingAs($superAdmin)
            ->get("/quotes/{$this->quote->id}/items/{$item->id}")
            ->assertStatus(404);
    });
});

describe('totals service', function () {
    test('recalculate refuses to run outside a transaction', function () {
        app(TotalsService::class)->recalculate($this->quote);
    })->throws(LogicException::class);
});

describe('send precondition', function () {
    test('a quote with no lines cannot be sent', function () {
        app(TransitionService::class)->send($this->quote, $this->adminUser());
    })->throws(ValidationException::class);
});

describe('import', function () {
    test('imports lines into a draft quote and links products by SKU', function () {
        $superAdmin = $this->superAdminUser();

        Product::factory()->create([
            'sku' => 'IMP-SKU',
            'name' => 'Imported product',
            'unit_price' => '12.00',
        ]);

        $csv = "sku,name,quantity,unit_price\nIMP-SKU,Imported product,2,12.00\n,Manual line,1,5.00\n,Bad line,0,5.00\n";
        $file = UploadedFile::fake()->createWithContent('lines.csv', $csv);

        $preview = $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items/import/preview", ['file' => $file])
            ->assertOk()
            ->json();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$this->quote->id}/items/import/commit", ['token' => $preview['token']])
            ->assertOk();

        expect(QuoteItem::where('quote_id', $this->quote->id)->count())->toBe(2)
            ->and($this->quote->fresh()->subtotal)->toBe('29.00');
    });

    test('user without permission cannot preview an import', function () {
        $user = $this->normalUser();
        $file = UploadedFile::fake()->createWithContent('lines.csv', "name,quantity,unit_price\nLine,1,1.00\n");

        $this->actingAs($user)
            ->postJson("/quotes/{$this->quote->id}/items/import/preview", ['file' => $file])
            ->assertStatus(403);
    });
});

describe('export', function () {
    test('exports a quote\'s lines without formula injection', function () {
        $superAdmin = $this->superAdminUser();

        QuoteItem::factory()->create([
            'quote_id' => $this->quote->id,
            'name' => '=HYPERLINK("http://evil")',
        ]);

        $response = $this->actingAs($superAdmin)
            ->get("/quotes/{$this->quote->id}/items/export");

        $response->assertOk();

        expect($response->streamedContent())->toContain("'=HYPERLINK");
    });

    test('user without permission cannot export quote items', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get("/quotes/{$this->quote->id}/items/export")
            ->assertStatus(403);
    });
});

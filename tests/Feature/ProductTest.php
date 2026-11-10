<?php

declare(strict_types=1);

use App\Models\Log;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

describe('index', function () {
    test('authenticated user with permission can list products', function () {
        $superAdmin = $this->superAdminUser();

        Product::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/products')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Index')
                ->has('products.data', 3)
            );
    });

    test('unauthenticated user cannot list products', function () {
        $this->get('/products')->assertRedirect('/login');
    });

    test('user without permission cannot list products', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/products')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/products/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Create')
                ->has('units')
                ->has('currencies')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/products/create')->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/products/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a product', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/products', [
                'sku' => 'WIDGET-1',
                'name' => 'Standard Widget',
                'description' => 'A standard widget.',
                'unit' => 'each',
                'unit_price' => '10.50',
                'cost_price' => '6.00',
                'tax_rate' => '20.00',
                'currency' => 'GBP',
                'is_active' => true,
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['sku' => 'WIDGET-1']);

        $this->assertDatabaseHas('products', [
            'sku' => 'WIDGET-1',
            'name' => 'Standard Widget',
        ]);
    });

    test('sku is normalised to upper case', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/products', [
                'sku' => 'abc-1',
                'name' => 'Lower Case Sku',
                'unit' => 'each',
                'unit_price' => '10.00',
                'tax_rate' => '20.00',
                'currency' => 'GBP',
                'is_active' => true,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('products', ['sku' => 'ABC-1']);
    });

    test('client supplied audit and tenant columns are ignored', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/products', [
                'sku' => 'SAFE-1',
                'name' => 'Safe Product',
                'unit' => 'each',
                'unit_price' => '10.00',
                'tax_rate' => '20.00',
                'currency' => 'GBP',
                'is_active' => true,
                'created_by' => 9999,
                'organisation_id' => 9999,
            ])
            ->assertStatus(201);

        $product = Product::query()->where('sku', 'SAFE-1')->firstOrFail();

        expect($product->created_by)->toBe($superAdmin->id)
            ->and($product->organisation_id)->not->toBe(9999);
    });

    test('user without permission cannot create a product', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/products', [
                'sku' => 'NOPE-1',
                'name' => 'Not Allowed',
                'unit' => 'each',
                'unit_price' => '10.00',
                'tax_rate' => '20.00',
                'currency' => 'GBP',
            ])
            ->assertStatus(403);

        $this->assertDatabaseMissing('products', ['sku' => 'NOPE-1']);
    });

    test('store fails validation when sku is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/products', [
                'name' => 'No Sku',
                'unit' => 'each',
                'unit_price' => '10.00',
                'tax_rate' => '20.00',
                'currency' => 'GBP',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    });

    test('store fails validation when name is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/products', [
                'sku' => 'NO-NAME',
                'unit' => 'each',
                'unit_price' => '10.00',
                'tax_rate' => '20.00',
                'currency' => 'GBP',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('store fails validation when sku is a duplicate within the same organisation', function () {
        $superAdmin = $this->superAdminUser();

        Product::factory()->create(['sku' => 'DUPE-1']);

        $this->actingAs($superAdmin)
            ->postJson('/products', [
                'sku' => 'DUPE-1',
                'name' => 'Duplicate',
                'unit' => 'each',
                'unit_price' => '10.00',
                'tax_rate' => '20.00',
                'currency' => 'GBP',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    });

    test('store fails validation when sku contains unsafe characters', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/products', [
                'sku' => '=cmd|calc',
                'name' => 'Unsafe Sku',
                'unit' => 'each',
                'unit_price' => '10.00',
                'tax_rate' => '20.00',
                'currency' => 'GBP',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    });

    test('store fails validation when unit_price is not numeric', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/products', [
                'sku' => 'BAD-PRICE',
                'name' => 'Bad Price',
                'unit' => 'each',
                'unit_price' => 'abc',
                'tax_rate' => '20.00',
                'currency' => 'GBP',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['unit_price']);
    });

    test('store fails validation when tax_rate is above 100', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/products', [
                'sku' => 'BAD-TAX',
                'name' => 'Bad Tax',
                'unit' => 'each',
                'unit_price' => '10.00',
                'tax_rate' => '100.01',
                'currency' => 'GBP',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tax_rate']);
    });

    test('logs product creation with actor id', function () {
        $actor = $this->adminUser();

        $this->actingAs($actor)
            ->postJson('/products', [
                'sku' => 'LOG-1',
                'name' => 'Logged Product',
                'unit' => 'each',
                'unit_price' => '10.00',
                'tax_rate' => '20.00',
                'currency' => 'GBP',
                'is_active' => true,
            ])
            ->assertStatus(201);

        $log = Log::query()
            ->where('action_id', Log::ACTION_CREATE_PRODUCT)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['after']);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a product', function () {
        $superAdmin = $this->superAdminUser();

        $product = Product::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/products/{$product->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Show')
                ->has('product')
            );
    });

    test('unauthenticated user cannot view a product', function () {
        $product = Product::factory()->create();

        $this->get("/products/{$product->id}")->assertRedirect('/login');
    });

    test('user without permission cannot view a product', function () {
        $user = $this->userWithNoPermissions();

        $product = Product::factory()->create();

        $this->actingAs($user)
            ->get("/products/{$product->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent product', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/products/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $product = Product::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/products/{$product->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Edit')
                ->has('product')
                ->has('units')
                ->has('currencies')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $product = Product::factory()->create();

        $this->get("/products/{$product->id}/edit")->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $product = Product::factory()->create();

        $this->actingAs($user)
            ->get("/products/{$product->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a product', function () {
        $superAdmin = $this->superAdminUser();

        $product = Product::factory()->create(['name' => 'Old Name']);

        $this->actingAs($superAdmin)
            ->putJson("/products/{$product->id}", ['name' => 'New Name'])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Name',
        ]);
    });

    test('patch verb also updates a product', function () {
        $superAdmin = $this->superAdminUser();

        $product = Product::factory()->create(['unit_price' => '5.00']);

        $this->actingAs($superAdmin)
            ->patchJson("/products/{$product->id}", ['unit_price' => '7.50'])
            ->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'unit_price' => '7.50',
        ]);
    });

    test('user without permission cannot update a product', function () {
        $user = $this->normalUser();

        $product = Product::factory()->create(['name' => 'Original']);

        $this->actingAs($user)
            ->putJson("/products/{$product->id}", ['name' => 'Hacked'])
            ->assertStatus(403);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Original']);
    });

    test('update allows a product to keep its own sku', function () {
        $superAdmin = $this->superAdminUser();

        $product = Product::factory()->create(['sku' => 'KEEP-1']);

        $this->actingAs($superAdmin)
            ->putJson("/products/{$product->id}", [
                'sku' => 'KEEP-1',
                'name' => 'Renamed',
            ])
            ->assertStatus(200);
    });

    test('update fails validation when sku belongs to another product', function () {
        $superAdmin = $this->superAdminUser();

        Product::factory()->create(['sku' => 'TAKEN-1']);
        $product = Product::factory()->create(['sku' => 'FREE-1']);

        $this->actingAs($superAdmin)
            ->putJson("/products/{$product->id}", ['sku' => 'TAKEN-1'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    });

    test('cost_price can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();

        $product = Product::factory()->create(['cost_price' => '4.00']);

        $this->actingAs($superAdmin)
            ->putJson("/products/{$product->id}", ['cost_price' => null])
            ->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'cost_price' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $product = Product::factory()->create([
            'description' => 'Original description.',
            'cost_price' => '4.00',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/products/{$product->id}", ['name' => 'Updated Name'])
            ->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'description' => 'Original description.',
            'cost_price' => '4.00',
        ]);
    });

    test('logs product updates with actor id', function () {
        $actor = $this->adminUser();

        $product = Product::factory()->create(['name' => 'Old Name']);

        $this->actingAs($actor)
            ->putJson("/products/{$product->id}", ['name' => 'New Name'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_PRODUCT)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a product', function () {
        $superAdmin = $this->superAdminUser();

        $product = Product::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/products/{$product->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    });

    test('user without permission cannot soft delete a product', function () {
        $user = $this->normalUser();

        $product = Product::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/products/{$product->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);
    });

    test('destroy returns 404 for a non-existent product', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/products/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted product', function () {
        $superAdmin = $this->superAdminUser();

        $product = Product::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/products/{$product->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a product', function () {
        $user = $this->normalUser();

        $product = Product::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/products/{$product->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a product that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $product = Product::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/products/{$product->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a soft-deleted product', function () {
        $superAdmin = $this->superAdminUser();

        $product = Product::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/products/{$product->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    });

    test('user without permission cannot force delete a product', function () {
        $user = $this->normalUser();

        $product = Product::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/products/{$product->id}/force")
            ->assertStatus(403);

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    });

    test('force delete returns 404 for a product that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $product = Product::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/products/{$product->id}/force")
            ->assertStatus(404);

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete products', function () {
        $superAdmin = $this->superAdminUser();

        $products = Product::factory()->count(3)->create();
        $ids = $products->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/products/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('products', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/products/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/products/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete products', function () {
        $user = $this->normalUser();

        $products = Product::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/products/bulk/delete', [
                'ids' => $products->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore products', function () {
        $superAdmin = $this->superAdminUser();

        $products = Product::factory()->count(3)->deleted()->create();
        $ids = $products->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/products/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('products', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/products/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/products/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore products', function () {
        $user = $this->normalUser();

        $products = Product::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/products/bulk/restore', [
                'ids' => $products->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('import', function () {
    test('authenticated user with permission can preview and commit a csv import', function () {
        Storage::fake();

        $superAdmin = $this->superAdminUser();

        $csv = "sku,name,unit_price\nIMP-1,Imported One,10.50\nIMP-2,Imported Two,abc\n";
        $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

        $preview = $this->actingAs($superAdmin)
            ->postJson('/products/import/preview', ['file' => $file])
            ->assertStatus(200)
            ->json();

        $this->actingAs($superAdmin)
            ->postJson('/products/import/commit', ['token' => $preview['token']])
            ->assertStatus(200);

        expect(Product::query()->where('sku', 'IMP-1')->exists())->toBeTrue()
            ->and(Product::query()->where('sku', 'IMP-2')->exists())->toBeFalse();
    });

    test('import preview fails validation when no file is supplied', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/products/import/preview', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    test('user without permission cannot preview an import', function () {
        Storage::fake();

        $user = $this->normalUser();

        $file = UploadedFile::fake()->createWithContent(
            'products.csv',
            "sku,name,unit_price\nNOPE-1,Not Allowed,10.00\n",
        );

        $this->actingAs($user)
            ->postJson('/products/import/preview', ['file' => $file])
            ->assertStatus(403);
    });
});

describe('export', function () {
    test('authenticated user with permission can export products', function () {
        $superAdmin = $this->superAdminUser();

        Product::factory()->create(['sku' => 'EXP-1']);

        $response = $this->actingAs($superAdmin)->get('/products/export');

        $response->assertStatus(200);

        expect($response->streamedContent())->toContain('EXP-1');
    });

    test('user without permission cannot export products', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/products/export')
            ->assertStatus(403);
    });

    test('csv export neutralises formula injection', function () {
        $superAdmin = $this->superAdminUser();

        Product::factory()->create(['name' => '=HYPERLINK("http://evil")']);

        $response = $this->actingAs($superAdmin)->get('/products/export');

        $response->assertStatus(200);

        expect($response->streamedContent())->toContain("'=HYPERLINK");
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted products', function () {
        $superAdmin = $this->superAdminUser();

        Product::factory()->count(2)->create();
        Product::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/products')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Index')
                ->has('products.data', 2)
            );
    });

    test('show returns 404 for a soft-deleted product', function () {
        $superAdmin = $this->superAdminUser();

        $product = Product::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/products/{$product->id}")
            ->assertStatus(404);
    });
});

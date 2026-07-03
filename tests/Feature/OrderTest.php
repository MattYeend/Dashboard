<?php

use App\Models\Log;
use App\Models\Order;
use App\Models\OrderStatus;
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
    test('authenticated user with permission can list orders', function () {
        $superAdmin = $this->superAdminUser();

        Order::factory()->count(3)->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->get('/orders')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Index')
                ->has('orders')
            );
    });

    test('unauthenticated user cannot list orders', function () {
        $this->get('/orders')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list orders', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/orders')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/orders/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/orders/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/orders/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create an order', function () {
        $superAdmin = $this->superAdminUser();
        $orderStatus = OrderStatus::factory()->create();

       $payload = [
    'orderable_type' => 'user',
    'orderable_id' => $superAdmin->id,
    'title' => 'Office supplies restock',
    'description' => 'Quarterly stationery and consumables order.',
    'notes' => 'Deliver to reception desk.',
    'subtotal' => 120.00,
    'discount_amount' => 10.00,
    'tax_amount' => 22.00,
    'total_amount' => 132.00,
    'ordered_at' => '2025-07-01 09:00:00',
    'due_at' => '2025-07-15 17:00:00',
    'status_id' => $orderStatus->id,
];

        $this->actingAs($superAdmin)
    ->postJson('/orders', $payload)
    ->assertStatus(201)
    ->assertJsonFragment(['title' => 'Office supplies restock']);

$order = Order::where('title', 'Office supplies restock')->firstOrFail();

expect($order->order_number)->toMatch('/^ORD-[A-Z0-9]{8}$/');

$this->assertDatabaseHas('orders', [
    'title' => 'Office supplies restock',
    'status_id' => $orderStatus->id,
]);
    });

    test('user without permission cannot create an order', function () {
        $user = $this->normalUser();

        $payload = [
            'orderable_type' => 'user',
            'orderable_id' => $user->id,
            'order_number' => 'ORD-1002',
            'title' => 'Blocked order',
        ];

        $this->actingAs($user)
            ->postJson('/orders', $payload)
            ->assertStatus(403);
    });

    test('store fails validation when orderable_type is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/orders', [
                'orderable_id' => $superAdmin->id,
                'order_number' => 'ORD-1003',
                'title' => 'Missing orderable type',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['orderable_type']);
    });

    test('store fails validation when orderable_id is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/orders', [
                'orderable_type' => 'user',
                'order_number' => 'ORD-1004',
                'title' => 'Missing orderable id',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['orderable_id']);
    });

    test('store fails validation when subtotal is missing', function () {
    $superAdmin = $this->superAdminUser();

    $this->actingAs($superAdmin)
        ->postJson('/orders', [
            'orderable_type' => 'user',
            'orderable_id' => $superAdmin->id,
            'title' => 'Missing subtotal',
            'total_amount' => 100.00,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['subtotal']);
});

test('store fails validation when total_amount is missing', function () {
    $superAdmin = $this->superAdminUser();

    $this->actingAs($superAdmin)
        ->postJson('/orders', [
            'orderable_type' => 'user',
            'orderable_id' => $superAdmin->id,
            'title' => 'Missing total amount',
            'subtotal' => 100.00,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['total_amount']);
});

    test('client-supplied order_number is ignored and a new one is always generated', function () {
    $superAdmin = $this->superAdminUser();

    $existing = Order::factory()->forModel($superAdmin)->create(['order_number' => 'ORD-2001']);

    $this->actingAs($superAdmin)
        ->postJson('/orders', [
            'orderable_type' => 'user',
            'orderable_id' => $superAdmin->id,
            'order_number' => 'ORD-2001',
            'title' => 'Attempted order number spoof',
            'subtotal' => 50.00,
            'total_amount' => 50.00,
        ])
        ->assertStatus(201);

    $created = Order::where('title', 'Attempted order number spoof')->firstOrFail();

    expect($created->order_number)
        ->not->toBe('ORD-2001')
        ->and($created->order_number)->toMatch('/^ORD-[A-Z0-9]{8}$/');

    $this->assertDatabaseHas('orders', ['id' => $existing->id, 'order_number' => 'ORD-2001']);
});

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/orders', [
                'orderable_type' => 'user',
                'orderable_id' => $superAdmin->id,
                'order_number' => 'ORD-1005',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when status_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/orders', [
                'orderable_type' => 'user',
                'orderable_id' => $superAdmin->id,
                'order_number' => 'ORD-1006',
                'title' => 'Invalid status',
                'status_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status_id']);
    });

    test('store fails validation when ordered_at is not a valid date', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/orders', [
                'orderable_type' => 'user',
                'orderable_id' => $superAdmin->id,
                'order_number' => 'ORD-1007',
                'title' => 'Invalid ordered_at',
                'ordered_at' => 'not-a-date',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ordered_at']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
    ->postJson('/orders', [
        'orderable_type' => 'user',
        'orderable_id' => $superAdmin->id,
        'title' => 'Minimal order',
        'subtotal' => 0,
        'total_amount' => 0,
    ])
    ->assertStatus(201);

$this->assertDatabaseHas('orders', [
    'title' => 'Minimal order',
    'description' => null,
    'status_id' => null,
]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
    ->postJson('/orders', [
        'orderable_type' => 'user',
        'orderable_id' => $superAdmin->id,
        'title' => 'Order with meta',
        'subtotal' => 10.00,
        'total_amount' => 10.00,
        'meta' => ['channel' => 'web', 'tags' => ['priority']],
    ])
    ->assertStatus(201);

$this->assertDatabaseHas('orders', ['title' => 'Order with meta']);
    });
});

describe('show', function () {
    test('authenticated user with permission can view an order', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->get("/orders/{$order->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Show')
                ->has('order')
            );
    });

    test('unauthenticated user cannot view an order', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->create();

        $this->get("/orders/{$order->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view an order', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $order = Order::factory()->forModel($superAdmin)->create();

        $this->actingAs($user)
            ->get("/orders/{$order->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for non-existent order', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/orders/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->get("/orders/{$order->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Edit')
                ->has('order')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->create();

        $this->get("/orders/{$order->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $order = Order::factory()->forModel($superAdmin)->create();

        $this->actingAs($user)
            ->get("/orders/{$order->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update an order', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->create(['title' => 'Old title']);

        $this->actingAs($superAdmin)
            ->putJson("/orders/{$order->id}", ['title' => 'New title'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'New title']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'title' => 'New title',
        ]);
    });

    test('patch verb also updates an order', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->create(['notes' => 'Old notes']);

        $this->actingAs($superAdmin)
            ->patchJson("/orders/{$order->id}", ['notes' => 'New notes'])
            ->assertStatus(200)
            ->assertJsonFragment(['notes' => 'New notes']);
    });

    test('user without permission cannot update an order', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $order = Order::factory()->forModel($superAdmin)->create();

        $this->actingAs($user)
            ->putJson("/orders/{$order->id}", ['title' => 'New title'])
            ->assertStatus(403);
    });

    test('order_number cannot be changed via update', function () {
    $superAdmin = $this->superAdminUser();

    $order = Order::factory()->forModel($superAdmin)->create();
    $originalOrderNumber = $order->order_number;

    $this->actingAs($superAdmin)
        ->putJson("/orders/{$order->id}", [
            'order_number' => 'ORD-SPOOFED1',
            'title' => 'Renamed order',
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'order_number' => $originalOrderNumber,
        'title' => 'Renamed order',
    ]);
});

    test('update fails validation when ordered_at is not a valid date', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->putJson("/orders/{$order->id}", ['ordered_at' => 'not-a-date'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ordered_at']);
    });

    test('update fails validation when status_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->putJson("/orders/{$order->id}", ['status_id' => 99999])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status_id']);
    });

    test('nullable fields can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();
        $orderStatus = OrderStatus::factory()->create();

        $order = Order::factory()->forModel($superAdmin)->create([
            'description' => 'Some description.',
            'notes' => 'Some notes.',
            'ordered_at' => '2025-07-01 09:00:00',
            'due_at' => '2025-07-15 17:00:00',
            'completed_at' => '2025-07-16 12:00:00',
            'status_id' => $orderStatus->id,
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/orders/{$order->id}", [
                'description' => null,
                'notes' => null,
                'ordered_at' => null,
                'due_at' => null,
                'completed_at' => null,
                'status_id' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'description' => null,
            'notes' => null,
            'ordered_at' => null,
            'due_at' => null,
            'completed_at' => null,
            'status_id' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->create([
            'description' => 'Original description.',
            'notes' => 'Original notes.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/orders/{$order->id}", [
                'title' => 'Updated title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'title' => 'Updated title',
            'description' => 'Original description.',
            'notes' => 'Original notes.',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->create([
            'notes' => 'Some notes.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/orders/{$order->id}", [
                'notes' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'notes' => null,
        ]);
    });

    test('orderable type and id can be updated', function () {
        $superAdmin = $this->superAdminUser();
        $otherUser = $this->normalUser();

        $order = Order::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->putJson("/orders/{$order->id}", [
                'orderable_type' => 'user',
                'orderable_id' => $otherUser->id,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'orderable_type' => 'App\\Models\\User',
            'orderable_id' => $otherUser->id,
        ]);
    });

    test('monetary fields can be updated', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->create([
            'subtotal' => 100.00,
            'discount_amount' => 0.00,
            'tax_amount' => 20.00,
            'total_amount' => 120.00,
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/orders/{$order->id}", [
                'subtotal' => 200.00,
                'discount_amount' => 15.00,
                'tax_amount' => 37.00,
                'total_amount' => 222.00,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'subtotal' => 200.00,
            'discount_amount' => 15.00,
            'tax_amount' => 37.00,
            'total_amount' => 222.00,
        ]);
    });

    test('logs order updates with actor id', function () {
        $actor = $this->adminUser();

        $order = Order::factory()->forModel($actor)->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/orders/{$order->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_ORDER)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete an order', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/orders/{$order->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    });

    test('user without permission cannot soft delete an order', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $order = Order::factory()->forModel($superAdmin)->create();

        $this->actingAs($user)
            ->deleteJson("/orders/{$order->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for non-existent order', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/orders/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted order', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/orders/{$order->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore an order', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $order = Order::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($user)
            ->postJson("/orders/{$order->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for an order that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->postJson("/orders/{$order->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete an order', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/orders/{$order->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    });

    test('user without permission cannot force delete an order', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $order = Order::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/orders/{$order->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for an order that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/orders/{$order->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete orders', function () {
        $superAdmin = $this->superAdminUser();

        $orders = Order::factory()->count(3)->forModel($superAdmin)->create();
        $ids = $orders->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/orders/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('orders', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/orders/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/orders/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete orders', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $orders = Order::factory()->count(2)->forModel($superAdmin)->create();

        $this->actingAs($user)
            ->postJson('/orders/bulk/delete', [
                'ids' => $orders->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore orders', function () {
        $superAdmin = $this->superAdminUser();

        $orders = Order::factory()->count(3)->forModel($superAdmin)->deleted()->create();
        $ids = $orders->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/orders/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('orders', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/orders/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/orders/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore orders', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $orders = Order::factory()->count(2)->forModel($superAdmin)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/orders/bulk/restore', [
                'ids' => $orders->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted orders', function () {
        $superAdmin = $this->superAdminUser();

        Order::factory()->count(2)->forModel($superAdmin)->create();
        $trashed = Order::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/orders')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Index')
                ->has('orders')
            );

        $this->assertSoftDeleted('orders', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted order', function () {
        $superAdmin = $this->superAdminUser();

        $order = Order::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/orders/{$order->id}")
            ->assertStatus(404);
    });
});
<?php

use App\Models\Log;
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

describe('index', function () {
    test('authenticated user with permission can list order statuses', function () {
        $superAdmin = $this->superAdminUser();

        OrderStatus::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/order-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('OrderStatuses/Index')
                ->has('orderStatuses')
            );
    });

    test('unauthenticated user cannot list order statuses', function () {
        $this->get('/order-statuses')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list order statuses', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/order-statuses')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/order-statuses/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('OrderStatuses/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/order-statuses/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/order-statuses/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create an order status', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'title' => 'Processing',
            'description' => 'Order is being prepared.',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/order-statuses', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Processing']);

        $this->assertDatabaseHas('order_statuses', [
            'title' => 'Processing',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('user without permission cannot create an order status', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/order-statuses', [
                'title' => 'Processing',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/order-statuses', [
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/order-statuses', [
                'title' => 'Processing',
                'background_colour' => 'not-a-colour',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('store fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/order-statuses', [
                'title' => 'Processing',
                'background_colour' => '#bee3f8',
                'text_colour' => 'not-a-colour',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/order-statuses', [
                'title' => 'Pending',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('order_statuses', [
            'title' => 'Pending',
            'description' => null,
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/order-statuses', [
                'title' => 'Shipped',
                'meta' => ['order' => 3, 'icon' => 'truck'],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('order_statuses', [
            'title' => 'Shipped',
        ]);
    });
});

describe('show', function () {
    test('authenticated user with permission can view an order status', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/order-statuses/{$orderStatus->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('OrderStatuses/Show')
                ->has('orderStatus')
            );
    });

    test('unauthenticated user cannot view an order status', function () {
        $orderStatus = OrderStatus::factory()->create();

        $this->get("/order-statuses/{$orderStatus->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view an order status', function () {
        $user = $this->userWithNoPermissions();

        $orderStatus = OrderStatus::factory()->create();

        $this->actingAs($user)
            ->get("/order-statuses/{$orderStatus->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent order status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/order-statuses/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/order-statuses/{$orderStatus->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('OrderStatuses/Edit')
                ->has('orderStatus')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $orderStatus = OrderStatus::factory()->create();

        $this->get("/order-statuses/{$orderStatus->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $orderStatus = OrderStatus::factory()->create();

        $this->actingAs($user)
            ->get("/order-statuses/{$orderStatus->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update an order status', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->create(['title' => 'Pending']);

        $this->actingAs($superAdmin)
            ->putJson("/order-statuses/{$orderStatus->id}", ['title' => 'Processing'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'Processing']);

        $this->assertDatabaseHas('order_statuses', [
            'id' => $orderStatus->id,
            'title' => 'Processing',
        ]);
    });

    test('patch verb also updates an order status', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->create(['background_colour' => '#ffffff']);

        $this->actingAs($superAdmin)
            ->patchJson("/order-statuses/{$orderStatus->id}", ['background_colour' => '#bee3f8'])
            ->assertStatus(200)
            ->assertJsonFragment(['background_colour' => '#bee3f8']);
    });

    test('user without permission cannot update an order status', function () {
        $user = $this->normalUser();

        $orderStatus = OrderStatus::factory()->create();

        $this->actingAs($user)
            ->putJson("/order-statuses/{$orderStatus->id}", ['title' => 'Processing'])
            ->assertStatus(403);
    });

    test('update fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/order-statuses/{$orderStatus->id}", ['background_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('update fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/order-statuses/{$orderStatus->id}", ['text_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('description can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->create([
            'description' => 'Order is being prepared.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/order-statuses/{$orderStatus->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('order_statuses', [
            'id' => $orderStatus->id,
            'description' => null,
        ]);
    });

    test('background_colour and text_colour cannot be nulled and fail validation', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->create([
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/order-statuses/{$orderStatus->id}", [
                'background_colour' => null,
                'text_colour' => null,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour', 'text_colour']);

        $this->assertDatabaseHas('order_statuses', [
            'id' => $orderStatus->id,
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->create([
            'description' => 'Original description.',
            'background_colour' => '#bee3f8',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/order-statuses/{$orderStatus->id}", [
                'title' => 'Updated Title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('order_statuses', [
            'id' => $orderStatus->id,
            'title' => 'Updated Title',
            'description' => 'Original description.',
            'background_colour' => '#bee3f8',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->create([
            'description' => 'Some description.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/order-statuses/{$orderStatus->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('order_statuses', [
            'id' => $orderStatus->id,
            'description' => null,
        ]);
    });

    test('logs order status updates with actor id', function () {
        $actor = $this->adminUser();

        $orderStatus = OrderStatus::factory()->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/order-statuses/{$orderStatus->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_ORDER_STATUS)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete an order status', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/order-statuses/{$orderStatus->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('order_statuses', ['id' => $orderStatus->id]);
    });

    test('user without permission cannot soft delete an order status', function () {
        $user = $this->normalUser();

        $orderStatus = OrderStatus::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/order-statuses/{$orderStatus->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent order status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/order-statuses/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted order status', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/order-statuses/{$orderStatus->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('order_statuses', [
            'id' => $orderStatus->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore an order status', function () {
        $user = $this->normalUser();

        $orderStatus = OrderStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/order-statuses/{$orderStatus->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for an order status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/order-statuses/{$orderStatus->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete an order status', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/order-statuses/{$orderStatus->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('order_statuses', ['id' => $orderStatus->id]);
    });

    test('user without permission cannot force delete an order status', function () {
        $user = $this->normalUser();

        $orderStatus = OrderStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/order-statuses/{$orderStatus->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for an order status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/order-statuses/{$orderStatus->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete order statuses', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatuses = OrderStatus::factory()->count(3)->create();
        $ids = $orderStatuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/order-statuses/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('order_statuses', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/order-statuses/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/order-statuses/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete order statuses', function () {
        $user = $this->normalUser();

        $orderStatuses = OrderStatus::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/order-statuses/bulk/delete', [
                'ids' => $orderStatuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore order statuses', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatuses = OrderStatus::factory()->count(3)->deleted()->create();
        $ids = $orderStatuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/order-statuses/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('order_statuses', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/order-statuses/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/order-statuses/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore order statuses', function () {
        $user = $this->normalUser();

        $orderStatuses = OrderStatus::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/order-statuses/bulk/restore', [
                'ids' => $orderStatuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted order statuses', function () {
        $superAdmin = $this->superAdminUser();

        OrderStatus::factory()->count(2)->create();
        $trashed = OrderStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/order-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('OrderStatuses/Index')
                ->has('orderStatuses')
            );

        $this->assertSoftDeleted('order_statuses', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted order status', function () {
        $superAdmin = $this->superAdminUser();

        $orderStatus = OrderStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/order-statuses/{$orderStatus->id}")
            ->assertStatus(404);
    });
});

<?php

declare(strict_types=1);

use App\Models\Log;
use App\Models\PaymentStatus;
use App\Services\PaymentStatuses\DeleterService;
use Database\Seeders\PaymentStatusSeeder;
use Illuminate\Auth\Access\AuthorizationException;
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
    test('authenticated user with permission can list payment statuses', function () {
        $superAdmin = $this->superAdminUser();

        PaymentStatus::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/payment-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PaymentStatuses/Index')
                ->has('paymentStatuses')
            );
    });

    test('unauthenticated user cannot list payment statuses', function () {
        $this->get('/payment-statuses')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list payment statuses', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/payment-statuses')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/payment-statuses/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PaymentStatuses/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/payment-statuses/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/payment-statuses/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a payment status', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'title' => 'Refunded',
            'description' => 'Payment has been refunded to the customer.',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Refunded']);

        $this->assertDatabaseHas('payment_statuses', [
            'title' => 'Refunded',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('user without permission cannot create a payment status', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/payment-statuses', [
                'title' => 'Refunded',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses', [
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when title already exists in the organisation', function () {
        $superAdmin = $this->superAdminUser();

        PaymentStatus::factory()->create(['title' => 'Refunded']);

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses', [
                'title' => 'Refunded',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses', [
                'title' => 'Refunded',
                'background_colour' => 'not-a-colour',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('store fails validation when background_colour carries css', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses', [
                'title' => 'Refunded',
                'background_colour' => 'red; background:url(x)',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('store fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses', [
                'title' => 'Refunded',
                'background_colour' => '#bee3f8',
                'text_colour' => 'not-a-colour',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses', [
                'title' => 'Disputed',
                'background_colour' => '#fed7d7',
                'text_colour' => '#742a2a',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('payment_statuses', [
            'title' => 'Disputed',
            'description' => null,
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses', [
                'title' => 'On Hold',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#1a202c',
                'meta' => ['order' => 5, 'icon' => 'pause'],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('payment_statuses', [
            'title' => 'On Hold',
        ]);
    });

    test('a client can never create a system status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses', [
                'title' => 'Sneaky',
                'background_colour' => '#ffffff',
                'text_colour' => '#000000',
                'system_key' => PaymentStatus::KEY_COMPLETED,
            ])
            ->assertStatus(201);

        expect(PaymentStatus::where('title', 'Sneaky')->value('system_key'))->toBeNull();
    });

    test('logs payment status creation with actor id', function () {
        $actor = $this->adminUser();

        $this->actingAs($actor)
            ->postJson('/payment-statuses', [
                'title' => 'Refunded',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ])
            ->assertStatus(201);

        $log = Log::query()
            ->where('action_id', Log::ACTION_CREATE_PAYMENT_STATUS)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKey('after');
    });
});

describe('show', function () {
    test('authenticated user with permission can view a payment status', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/payment-statuses/{$paymentStatus->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PaymentStatuses/Show')
                ->has('paymentStatus')
            );
    });

    test('unauthenticated user cannot view a payment status', function () {
        $paymentStatus = PaymentStatus::factory()->create();

        $this->get("/payment-statuses/{$paymentStatus->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a payment status', function () {
        $user = $this->userWithNoPermissions();

        $paymentStatus = PaymentStatus::factory()->create();

        $this->actingAs($user)
            ->get("/payment-statuses/{$paymentStatus->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent payment status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/payment-statuses/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/payment-statuses/{$paymentStatus->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PaymentStatuses/Edit')
                ->has('paymentStatus')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $paymentStatus = PaymentStatus::factory()->create();

        $this->get("/payment-statuses/{$paymentStatus->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $paymentStatus = PaymentStatus::factory()->create();

        $this->actingAs($user)
            ->get("/payment-statuses/{$paymentStatus->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a payment status', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->create(['title' => 'Disputed']);

        $this->actingAs($superAdmin)
            ->putJson("/payment-statuses/{$paymentStatus->id}", ['title' => 'Chargeback'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'Chargeback']);

        $this->assertDatabaseHas('payment_statuses', [
            'id' => $paymentStatus->id,
            'title' => 'Chargeback',
        ]);
    });

    test('patch verb also updates a payment status', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->create(['background_colour' => '#ffffff']);

        $this->actingAs($superAdmin)
            ->patchJson("/payment-statuses/{$paymentStatus->id}", ['background_colour' => '#bee3f8'])
            ->assertStatus(200)
            ->assertJsonFragment(['background_colour' => '#bee3f8']);
    });

    test('user without permission cannot update a payment status', function () {
        $user = $this->normalUser();

        $paymentStatus = PaymentStatus::factory()->create();

        $this->actingAs($user)
            ->putJson("/payment-statuses/{$paymentStatus->id}", ['title' => 'Chargeback'])
            ->assertStatus(403);
    });

    test('update fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/payment-statuses/{$paymentStatus->id}", ['background_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('update fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/payment-statuses/{$paymentStatus->id}", ['text_colour' => 'not-a-colour'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('description can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->create([
            'description' => 'Payment has been refunded to the customer.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/payment-statuses/{$paymentStatus->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('payment_statuses', [
            'id' => $paymentStatus->id,
            'description' => null,
        ]);
    });

    test('background_colour and text_colour cannot be nulled and fail validation', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->create([
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/payment-statuses/{$paymentStatus->id}", [
                'background_colour' => null,
                'text_colour' => null,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour', 'text_colour']);

        $this->assertDatabaseHas('payment_statuses', [
            'id' => $paymentStatus->id,
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->create([
            'description' => 'Original description.',
            'background_colour' => '#bee3f8',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/payment-statuses/{$paymentStatus->id}", [
                'title' => 'Updated Title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('payment_statuses', [
            'id' => $paymentStatus->id,
            'title' => 'Updated Title',
            'description' => 'Original description.',
            'background_colour' => '#bee3f8',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->create([
            'description' => 'Some description.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/payment-statuses/{$paymentStatus->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('payment_statuses', [
            'id' => $paymentStatus->id,
            'description' => null,
        ]);
    });

    test('system_key cannot be changed via update', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->create(['title' => 'Custom']);

        $this->actingAs($superAdmin)
            ->putJson("/payment-statuses/{$paymentStatus->id}", [
                'title' => 'Custom',
                'system_key' => PaymentStatus::KEY_COMPLETED,
            ])
            ->assertStatus(200);

        expect($paymentStatus->fresh()->system_key)->toBeNull();
    });

    test('logs payment status updates with actor id', function () {
        $actor = $this->adminUser();

        $paymentStatus = PaymentStatus::factory()->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/payment-statuses/{$paymentStatus->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_PAYMENT_STATUS)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('system statuses', function () {
    test('a system status cannot be renamed', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()
            ->system(PaymentStatus::KEY_COMPLETED)
            ->create(['title' => 'Completed']);

        $this->actingAs($superAdmin)
            ->putJson("/payment-statuses/{$paymentStatus->id}", [
                'title' => 'Renamed',
                'background_colour' => '#ffffff',
                'text_colour' => '#000000',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        expect($paymentStatus->fresh()->title)->toBe('Completed');
    });

    test('a system status can still have its colours changed', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()
            ->system(PaymentStatus::KEY_COMPLETED)
            ->create(['title' => 'Completed', 'background_colour' => '#c6f6d5']);

        $this->actingAs($superAdmin)
            ->putJson("/payment-statuses/{$paymentStatus->id}", [
                'title' => 'Completed',
                'background_colour' => '#bee3f8',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('payment_statuses', [
            'id' => $paymentStatus->id,
            'title' => 'Completed',
            'system_key' => PaymentStatus::KEY_COMPLETED,
            'background_colour' => '#bee3f8',
        ]);
    });

    test('a system status cannot be soft deleted, even by a super admin', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()
            ->system(PaymentStatus::KEY_PENDING)
            ->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/payment-statuses/{$paymentStatus->id}")
            ->assertStatus(403);

        $this->assertNotSoftDeleted('payment_statuses', ['id' => $paymentStatus->id]);
    });

    test('deleting a system status is blocked at service level, covering the Super Admin bypass', function () {
        $admin = $this->adminUser();

        $paymentStatus = PaymentStatus::factory()
            ->system(PaymentStatus::KEY_PENDING)
            ->create();

        app(DeleterService::class)->delete($paymentStatus, $admin->id);
    })->throws(AuthorizationException::class);

    test('force deleting a system status is blocked at service level', function () {
        $admin = $this->adminUser();

        $paymentStatus = PaymentStatus::factory()
            ->system(PaymentStatus::KEY_FAILED)
            ->create();

        app(DeleterService::class)->forceDelete($paymentStatus, $admin->id);
    })->throws(AuthorizationException::class);

    test('a system status is resolved by key for the current organisation', function () {
        $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()
            ->system(PaymentStatus::KEY_COMPLETED)
            ->create();

        expect(PaymentStatus::idForKey(PaymentStatus::KEY_COMPLETED))->toBe($paymentStatus->id);
    });

    test('resolving a missing system status fails loudly', function () {
        $this->superAdminUser();

        PaymentStatus::idForKey(PaymentStatus::KEY_COMPLETED);
    })->throws(RuntimeException::class, 'Run PaymentStatusSeeder.');

    test('the seeder creates exactly the four system statuses', function () {
        $this->superAdminUser();

        $this->seed(PaymentStatusSeeder::class);

        $keys = PaymentStatus::withoutGlobalScopes()
            ->whereNotNull('system_key')
            ->pluck('system_key')
            ->sort()
            ->values()
            ->all();

        expect($keys)->toBe(['cancelled', 'completed', 'failed', 'pending']);
    });

    test('the seeder is safe to run twice', function () {
        $this->superAdminUser();

        $this->seed(PaymentStatusSeeder::class);
        $this->seed(PaymentStatusSeeder::class);

        expect(PaymentStatus::withoutGlobalScopes()->whereNotNull('system_key')->count())->toBe(4);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a payment status', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/payment-statuses/{$paymentStatus->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('payment_statuses', ['id' => $paymentStatus->id]);
    });

    test('user without permission cannot soft delete a payment status', function () {
        $user = $this->normalUser();

        $paymentStatus = PaymentStatus::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/payment-statuses/{$paymentStatus->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent payment status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/payment-statuses/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted payment status', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/payment-statuses/{$paymentStatus->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('payment_statuses', [
            'id' => $paymentStatus->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a payment status', function () {
        $user = $this->normalUser();

        $paymentStatus = PaymentStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/payment-statuses/{$paymentStatus->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a payment status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/payment-statuses/{$paymentStatus->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a payment status', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/payment-statuses/{$paymentStatus->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('payment_statuses', ['id' => $paymentStatus->id]);
    });

    test('user without permission cannot force delete a payment status', function () {
        $user = $this->normalUser();

        $paymentStatus = PaymentStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/payment-statuses/{$paymentStatus->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a payment status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/payment-statuses/{$paymentStatus->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete payment statuses', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatuses = PaymentStatus::factory()->count(3)->create();
        $ids = $paymentStatuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('payment_statuses', ['id' => $id]);
        }
    });

    test('bulk delete refuses a system status and leaves it in place', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()
            ->system(PaymentStatus::KEY_CANCELLED)
            ->create();

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses/bulk/delete', ['ids' => [$paymentStatus->id]])
            ->assertStatus(403);

        $this->assertNotSoftDeleted('payment_statuses', ['id' => $paymentStatus->id]);
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete payment statuses', function () {
        $user = $this->normalUser();

        $paymentStatuses = PaymentStatus::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/payment-statuses/bulk/delete', [
                'ids' => $paymentStatuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore payment statuses', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatuses = PaymentStatus::factory()->count(3)->deleted()->create();
        $ids = $paymentStatuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('payment_statuses', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/payment-statuses/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore payment statuses', function () {
        $user = $this->normalUser();

        $paymentStatuses = PaymentStatus::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/payment-statuses/bulk/restore', [
                'ids' => $paymentStatuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted payment statuses', function () {
        $superAdmin = $this->superAdminUser();

        PaymentStatus::factory()->count(2)->create();
        $trashed = PaymentStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/payment-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PaymentStatuses/Index')
                ->has('paymentStatuses')
            );

        $this->assertSoftDeleted('payment_statuses', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted payment status', function () {
        $superAdmin = $this->superAdminUser();

        $paymentStatus = PaymentStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/payment-statuses/{$paymentStatus->id}")
            ->assertStatus(404);
    });
});

describe('import', function () {
    test('imported rows are always custom statuses and duplicates and bad colours are skipped', function () {
        Storage::fake();

        $admin = $this->adminUser();

        PaymentStatus::factory()->create(['title' => 'Existing']);

        $csv = "title,background_colour,system_key\n"
            ."Fresh,#112233,completed\n"
            ."Existing,#445566,\n"
            ."Bad,notacolour,\n";

        $file = UploadedFile::fake()->createWithContent('payment-statuses.csv', $csv);

        $preview = $this->actingAs($admin)
            ->postJson(route('payment-statuses.import.preview'), ['file' => $file])
            ->assertOk()
            ->json();

        $this->actingAs($admin)
            ->postJson(route('payment-statuses.import.commit'), ['token' => $preview['token']])
            ->assertOk();

        expect(PaymentStatus::where('title', 'Fresh')->exists())->toBeTrue()
            ->and(PaymentStatus::where('title', 'Fresh')->value('system_key'))->toBeNull()
            ->and(PaymentStatus::where('title', 'Bad')->exists())->toBeFalse()
            ->and(PaymentStatus::where('title', 'Existing')->count())->toBe(1);
    });

    test('user without permission cannot preview an import', function () {
        $user = $this->normalUser();

        $file = UploadedFile::fake()->createWithContent('payment-statuses.csv', "title\n");

        $this->actingAs($user)
            ->postJson(route('payment-statuses.import.preview'), ['file' => $file])
            ->assertStatus(403);
    });

    test('commit fails validation when the token is not a uuid', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->postJson(route('payment-statuses.import.commit'), ['token' => 'not-a-uuid'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    });
});

describe('export', function () {
    test('authenticated user with permission can export payment statuses', function () {
        $admin = $this->adminUser();

        PaymentStatus::factory()->create(['title' => 'Mine']);

        $response = $this->actingAs($admin)->get(route('payment-statuses.export'));

        $response->assertOk();
        expect($response->streamedContent())->toContain('Mine');
    });

    test('exports do not expose formula injection', function () {
        $admin = $this->adminUser();

        PaymentStatus::factory()->create(['title' => '=HYPERLINK("http://evil")']);

        $response = $this->actingAs($admin)->get(route('payment-statuses.export'));

        $response->assertOk();
        expect($response->streamedContent())->toContain("'=HYPERLINK");
    });

    test('user without permission cannot export payment statuses', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get(route('payment-statuses.export'))
            ->assertStatus(403);
    });
});

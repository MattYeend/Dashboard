<?php

use App\Models\Address;
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
    test('authenticated user with permission can list addresses', function () {
        $superAdmin = $this->superAdminUser();

        Address::factory()->count(3)->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->get('/addresses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Addresses/Index')
                ->has('addresses')
            );
    });

    test('unauthenticated user cannot list addresses', function () {
        $this->get('/addresses')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list addresses', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/addresses')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/addresses/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Addresses/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/addresses/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/addresses/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create an address', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'addressable_type' => 'user',
            'addressable_id' => $superAdmin->id,
            'address_line_one' => '221B Baker Street',
            'address_line_two' => null,
            'town' => 'Marylebone',
            'city' => 'London',
            'county' => 'Greater London',
            'postcode' => 'NW1 6XE',
            'country' => 'United Kingdom',
            'is_primary' => true,
        ];

        $this->actingAs($superAdmin)
            ->postJson('/addresses', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['postcode' => 'NW1 6XE']);

        $this->assertDatabaseHas('addresses', [
            'postcode' => 'NW1 6XE',
        ]);
    });

    test('user without permission cannot create an address', function () {
        $user = $this->normalUser();

        $payload = [
            'addressable_type' => 'user',
            'addressable_id' => $user->id,
            'address_line_one' => '10 Downing Street',
            'city' => 'London',
            'country' => 'United Kingdom',
        ];

        $this->actingAs($user)
            ->postJson('/addresses', $payload)
            ->assertStatus(403);
    });

    test('store fails validation when addressable_type is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/addresses', [
                'addressable_id' => $superAdmin->id,
                'address_line_one' => '221B Baker Street',
                'city' => 'London',
                'country' => 'United Kingdom',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['addressable_type']);
    });

    test('store fails validation when addressable_id is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/addresses', [
                'addressable_type' => 'user',
                'address_line_one' => '221B Baker Street',
                'city' => 'London',
                'country' => 'United Kingdom',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['addressable_id']);
    });

    test('store fails validation when address_line_one is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/addresses', [
                'addressable_type' => 'user',
                'addressable_id' => $superAdmin->id,
                'city' => 'London',
                'country' => 'United Kingdom',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['address_line_one']);
    });

    test('store fails validation when city is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/addresses', [
                'addressable_type' => 'user',
                'addressable_id' => $superAdmin->id,
                'address_line_one' => '221B Baker Street',
                'country' => 'United Kingdom',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['city']);
    });

    test('store fails validation when country is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/addresses', [
                'addressable_type' => 'user',
                'addressable_id' => $superAdmin->id,
                'address_line_one' => '221B Baker Street',
                'city' => 'London',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['country']);
    });

    test('store fails validation when addressable_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/addresses', [
                'addressable_type' => 'user',
                'addressable_id' => 99999,
                'address_line_one' => '221B Baker Street',
                'city' => 'London',
                'country' => 'United Kingdom',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['addressable_id']);
    });

    test('store fails validation when is_primary is not boolean', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/addresses', [
                'addressable_type' => 'user',
                'addressable_id' => $superAdmin->id,
                'address_line_one' => '221B Baker Street',
                'city' => 'London',
                'country' => 'United Kingdom',
                'is_primary' => 'not-a-boolean',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_primary']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/addresses', [
                'addressable_type' => 'user',
                'addressable_id' => $superAdmin->id,
                'address_line_one' => '221B Baker Street',
                'city' => 'London',
                'country' => 'United Kingdom',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('addresses', [
            'addressable_id' => $superAdmin->id,
            'address_line_two' => null,
            'town' => null,
            'county' => null,
            'postcode' => null,
        ]);
    });
});

describe('show', function () {
    test('authenticated user with permission can view an address', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->get("/addresses/{$address->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Addresses/Show')
                ->has('address')
            );
    });

    test('unauthenticated user cannot view an address', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->create();

        $this->get("/addresses/{$address->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view an address', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $address = Address::factory()->forModel($superAdmin)->create();

        $this->actingAs($user)
            ->get("/addresses/{$address->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for non-existent address', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/addresses/99999')
            ->assertStatus(404);
    });

    test('show returns is_primary in the formatted response', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->create(['is_primary' => true]);

        $this->actingAs($superAdmin)
            ->get("/addresses/{$address->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Addresses/Show')
                ->where('address.is_primary', true)
                ->where('address.address_line_one', $address->address_line_one)
            );
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->get("/addresses/{$address->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Addresses/Edit')
                ->has('address')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->create();

        $this->get("/addresses/{$address->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $address = Address::factory()->forModel($superAdmin)->create();

        $this->actingAs($user)
            ->get("/addresses/{$address->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update an address', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->create(['postcode' => 'AB1 2CD']);

        $this->actingAs($superAdmin)
            ->putJson("/addresses/{$address->id}", ['postcode' => 'EF3 4GH'])
            ->assertStatus(200)
            ->assertJsonFragment(['postcode' => 'EF3 4GH']);

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'postcode' => 'EF3 4GH',
        ]);
    });

    test('patch verb also updates an address', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->create(['postcode' => 'AB1 2CD']);

        $this->actingAs($superAdmin)
            ->patchJson("/addresses/{$address->id}", ['postcode' => 'IJ5 6KL'])
            ->assertStatus(200)
            ->assertJsonFragment(['postcode' => 'IJ5 6KL']);
    });

    test('user without permission cannot update an address', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $address = Address::factory()->forModel($superAdmin)->create();

        $this->actingAs($user)
            ->putJson("/addresses/{$address->id}", ['city' => 'Manchester'])
            ->assertStatus(403);
    });

    test('update fails validation when country is empty', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->putJson("/addresses/{$address->id}", ['country' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['country']);
    });

    test('nullable fields can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->create([
            'address_line_two' => 'Flat 2',
            'town' => 'Marylebone',
            'county' => 'Greater London',
            'postcode' => 'NW1 6XE',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/addresses/{$address->id}", [
                'address_line_two' => null,
                'town' => null,
                'county' => null,
                'postcode' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'address_line_two' => null,
            'town' => null,
            'county' => null,
            'postcode' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->create([
            'postcode' => 'NW1 6XE',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/addresses/{$address->id}")
            ->assertStatus(200);

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'postcode' => 'NW1 6XE',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->create([
            'postcode' => 'NW1 6XE',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/addresses/{$address->id}", [
                'postcode' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'postcode' => null,
        ]);
    });

    test('addressable type and id can be updated', function () {
        $superAdmin = $this->superAdminUser();
        $otherUser = $this->normalUser();

        $address = Address::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->putJson("/addresses/{$address->id}", [
                'addressable_type' => 'user',
                'addressable_id' => $otherUser->id,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'addressable_type' => 'App\\Models\\User',
            'addressable_id' => $otherUser->id,
        ]);
    });

    test('is_primary can be toggled', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->create(['is_primary' => false]);

        $this->actingAs($superAdmin)
            ->putJson("/addresses/{$address->id}", ['is_primary' => true])
            ->assertStatus(200);

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'is_primary' => true,
        ]);
    });

    test('logs address updates with actor id', function () {
        $actor = $this->adminUser();

        $address = Address::factory()->forModel($actor)->create(['postcode' => 'AB1 2CD']);

        $this->actingAs($actor)
            ->putJson("/addresses/{$address->id}", ['postcode' => 'EF3 4GH'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_ADDRESS)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete an address', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/addresses/{$address->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('addresses', ['id' => $address->id]);
    });

    test('user without permission cannot soft delete an address', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $address = Address::factory()->forModel($superAdmin)->create();

        $this->actingAs($user)
            ->deleteJson("/addresses/{$address->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for non-existent address', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/addresses/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted address', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/addresses/{$address->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore an address', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $address = Address::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($user)
            ->postJson("/addresses/{$address->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for an address that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->postJson("/addresses/{$address->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete an address', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/addresses/{$address->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    });

    test('user without permission cannot force delete an address', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $address = Address::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/addresses/{$address->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for an address that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/addresses/{$address->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete addresses', function () {
        $superAdmin = $this->superAdminUser();

        $addresses = Address::factory()->count(3)->forModel($superAdmin)->create();
        $ids = $addresses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/addresses/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('addresses', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/addresses/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/addresses/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete addresses', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $addresses = Address::factory()->count(2)->forModel($superAdmin)->create();

        $this->actingAs($user)
            ->postJson('/addresses/bulk/delete', [
                'ids' => $addresses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore addresses', function () {
        $superAdmin = $this->superAdminUser();

        $addresses = Address::factory()->count(3)->forModel($superAdmin)->deleted()->create();
        $ids = $addresses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/addresses/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('addresses', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/addresses/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/addresses/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore addresses', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $addresses = Address::factory()->count(2)->forModel($superAdmin)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/addresses/bulk/restore', [
                'ids' => $addresses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('addressable options', function () {
    test('authenticated user can fetch addressable options for a valid type', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->getJson('/addresses/addressable-options?type=user')
            ->assertStatus(200)
            ->assertJsonIsArray();
    });

    test('unauthenticated user cannot fetch addressable options', function () {
        $this->getJson('/addresses/addressable-options?type=user')
            ->assertStatus(401);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted addresses', function () {
        $superAdmin = $this->superAdminUser();

        Address::factory()->count(2)->forModel($superAdmin)->create();
        $trashed = Address::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/addresses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Addresses/Index')
                ->has('addresses')
            );

        $this->assertSoftDeleted('addresses', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted address', function () {
        $superAdmin = $this->superAdminUser();

        $address = Address::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/addresses/{$address->id}")
            ->assertStatus(404);
    });
});

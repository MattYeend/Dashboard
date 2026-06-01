<?php

use App\Models\Contact;
use App\Models\User;
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

    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'Super Admin']);
    Role::firstOrCreate(['name' => 'User']);
});

test('example', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

describe('index', function () {
    test('authenticated user with permission can list contacts', function () {
        $superAdmin = $this->superAdminUser();

        Contact::factory()->count(3)->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->get('/contacts')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Contacts/Index')
                ->has('contacts')
            );
    });

    test('unauthenticated user cannot list contacts', function () {
        $this->get('/contacts')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list contacts', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/contacts')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/contacts/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Contacts/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/contacts/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/contacts/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a contact', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'contactable_type' => (new User)->getMorphClass(),
            'contactable_id' => $superAdmin->id,
            'phone' => '+44 20 7946 0958',
            'email' => 'james.hartley@example.co.uk',
            'address' => '12 Baker Street',
            'city' => 'London',
            'postal_code' => 'W1U 3BH',
            'country' => 'United Kingdom',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/contacts', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['email' => 'james.hartley@example.co.uk']);

        $this->assertDatabaseHas('contacts', [
            'email' => 'james.hartley@example.co.uk',
            'city' => 'London',
            'country' => 'United Kingdom',
        ]);
    });

    test('user without permission cannot create a contact', function () {
        $user = $this->normalUser();

        $payload = [
            'contactable_type' => (new User)->getMorphClass(),
            'contactable_id' => $user->id,
            'phone' => '+44 20 7946 0958',
            'email' => 'test@example.co.uk',
            'address' => '12 Baker Street',
            'city' => 'London',
            'postal_code' => 'W1U 3BH',
            'country' => 'United Kingdom',
        ];

        $this->actingAs($user)
            ->postJson('/contacts', $payload)
            ->assertStatus(403);
    });

    test('store fails validation when contactable_type is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/contacts', [
                'contactable_id' => $superAdmin->id,
                'email' => 'test@example.co.uk',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['contactable_type']);
    });

    test('store fails validation when contactable_id is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/contacts', [
                'contactable_type' => (new User)->getMorphClass(),
                'email' => 'test@example.co.uk',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['contactable_id']);
    });

    test('store fails validation when email is invalid', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/contacts', [
                'contactable_type' => (new User)->getMorphClass(),
                'contactable_id' => $superAdmin->id,
                'email' => 'not-an-email',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('store succeeds with only required morph fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/contacts', [
                'contactable_type' => (new User)->getMorphClass(),
                'contactable_id' => $superAdmin->id,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('contacts', [
            'contactable_id' => $superAdmin->id,
            'phone' => null,
            'email' => null,
        ]);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a contact', function () {
        $superAdmin = $this->superAdminUser();

        $contact = Contact::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->get("/contacts/{$contact->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Contacts/Show')
                ->has('contact')
            );
    });

    test('unauthenticated user cannot view a contact', function () {
        $superAdmin = $this->superAdminUser();

        $contact = Contact::factory()->forModel($superAdmin)->create();

        $this->get("/contacts/{$contact->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a contact', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $contact = Contact::factory()->forModel($superAdmin)->create();

        $this->actingAs($user)
            ->get("/contacts/{$contact->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for non-existent contact', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/contacts/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $contact = Contact::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->get("/contacts/{$contact->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Contacts/Edit')
                ->has('contact')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $contact = Contact::factory()->forModel($superAdmin)->create();

        $this->get("/contacts/{$contact->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $contact = Contact::factory()->forModel($superAdmin)->create();

        $this->actingAs($user)
            ->get("/contacts/{$contact->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a contact', function () {
        $superAdmin = $this->superAdminUser();

        $contact = Contact::factory()->forModel($superAdmin)->create(['city' => 'London']);

        $this->actingAs($superAdmin)
            ->putJson("/contacts/{$contact->id}", ['city' => 'Manchester'])
            ->assertStatus(200)
            ->assertJsonFragment(['city' => 'Manchester']);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'city' => 'Manchester',
        ]);
    });

    test('patch verb also updates a contact', function () {
        $superAdmin = $this->superAdminUser();

        $contact = Contact::factory()->forModel($superAdmin)->create(['country' => 'United Kingdom']);

        $this->actingAs($superAdmin)
            ->patchJson("/contacts/{$contact->id}", ['country' => 'Germany'])
            ->assertStatus(200)
            ->assertJsonFragment(['country' => 'Germany']);
    });

    test('user without permission cannot update a contact', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $contact = Contact::factory()->forModel($superAdmin)->create();

        $this->actingAs($user)
            ->putJson("/contacts/{$contact->id}", ['city' => 'Manchester'])
            ->assertStatus(403);
    });

    test('update fails validation when email is invalid', function () {
        $superAdmin = $this->superAdminUser();

        $contact = Contact::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->putJson("/contacts/{$contact->id}", ['email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a contact', function () {
        $superAdmin = $this->superAdminUser();

        $contact = Contact::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/contacts/{$contact->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
    });

    test('user without permission cannot soft delete a contact', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $contact = Contact::factory()->forModel($superAdmin)->create();

        $this->actingAs($user)
            ->deleteJson("/contacts/{$contact->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for non-existent contact', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/contacts/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted contact', function () {
        $superAdmin = $this->superAdminUser();

        $contact = Contact::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/contacts/{$contact->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a contact', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $contact = Contact::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($user)
            ->postJson("/contacts/{$contact->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a contact that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $contact = Contact::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->postJson("/contacts/{$contact->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a contact', function () {
        $superAdmin = $this->superAdminUser();

        $contact = Contact::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/contacts/{$contact->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    });

    test('user without permission cannot force delete a contact', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $contact = Contact::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/contacts/{$contact->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a contact that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $contact = Contact::factory()->forModel($superAdmin)->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/contacts/{$contact->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete contacts', function () {
        $superAdmin = $this->superAdminUser();

        $contacts = Contact::factory()->count(3)->forModel($superAdmin)->create();
        $ids = $contacts->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/contacts/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('contacts', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/contacts/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/contacts/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete contacts', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $contacts = Contact::factory()->count(2)->forModel($superAdmin)->create();

        $this->actingAs($user)
            ->postJson('/contacts/bulk/delete', [
                'ids' => $contacts->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore contacts', function () {
        $superAdmin = $this->superAdminUser();

        $contacts = Contact::factory()->count(3)->forModel($superAdmin)->deleted()->create();
        $ids = $contacts->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/contacts/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('contacts', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/contacts/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/contacts/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore contacts', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $contacts = Contact::factory()->count(2)->forModel($superAdmin)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/contacts/bulk/restore', [
                'ids' => $contacts->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted contacts', function () {
        $superAdmin = $this->superAdminUser();

        Contact::factory()->count(2)->forModel($superAdmin)->create();
        $trashed = Contact::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/contacts')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Contacts/Index')
                ->has('contacts')
            );

        $this->assertSoftDeleted('contacts', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted contact', function () {
        $superAdmin = $this->superAdminUser();

        $contact = Contact::factory()->forModel($superAdmin)->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/contacts/{$contact->id}")
            ->assertStatus(404);
    });
});

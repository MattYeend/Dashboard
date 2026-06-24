<?php

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

    Role::firstOrCreate(['name' => 'Admin']);
    Role::firstOrCreate(['name' => 'Super Admin']);
    Role::firstOrCreate(['name' => 'User']);
});

test('example', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

describe('index', function () {
    test('authenticated user with permission can list users', function () {
        $superAdmin = $this->superAdminUser();

        User::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/users')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Index')
                ->has('users')
            );
    });

    test('unauthenticated user cannot list users', function () {
        $this->get('/users')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list users', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/users')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/users/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/users/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/users/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a user', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'name' => 'James Hartley',
            'email' => 'james.hartley@example.co.uk',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/users', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['email' => 'james.hartley@example.co.uk']);

        $this->assertDatabaseHas('users', [
            'email' => 'james.hartley@example.co.uk',
            'name' => 'James Hartley',
        ]);
    });

    test('user without permission cannot create a user', function () {
        $user = $this->normalUser();

        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.co.uk',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $this->actingAs($user)
            ->postJson('/users', $payload)
            ->assertStatus(403);
    });

    test('store fails validation when name is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/users', [
                'email' => 'test@example.co.uk',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('store fails validation when password is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/users', [
                'name' => 'Test User',
                'email' => 'test@example.co.uk',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('store fails validation when email is invalid', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/users', [
                'name' => 'Test User',
                'email' => 'not-an-email',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('store fails validation when role is invalid', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/users', [
                'name' => 'Test User',
                'email' => 'test@example.co.uk',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'invalid_role',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a user', function () {
        $superAdmin = $this->superAdminUser();

        $user = User::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/users/{$user->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Show')
                ->has('user')
            );
    });

    test('unauthenticated user cannot view a user', function () {
        $user = User::factory()->create();

        $this->get("/users/{$user->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a user', function () {
        $normalUser = $this->normalUser();
        $target = User::factory()->create();

        $this->actingAs($normalUser)
            ->get("/users/{$target->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for non-existent user', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/users/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $user = User::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/users/{$user->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Edit')
                ->has('user')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $user = User::factory()->create();

        $this->get("/users/{$user->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $normalUser = $this->normalUser();
        $target = User::factory()->create();

        $this->actingAs($normalUser)
            ->get("/users/{$target->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a user', function () {
        $superAdmin = $this->superAdminUser();

        $user = User::factory()->create(['name' => 'Old Name']);

        $this->actingAs($superAdmin)
            ->putJson("/users/{$user->id}", ['name' => 'New Name'])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
        ]);
    });

    test('patch verb also updates a user', function () {
        $superAdmin = $this->superAdminUser();

        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($superAdmin)
            ->patchJson("/users/{$user->id}", ['role' => 'admin'])
            ->assertStatus(200)
            ->assertJsonFragment(['role' => 'admin']);
    });

    test('user without permission cannot update a user', function () {
        $normalUser = $this->normalUser();
        $target = User::factory()->create();

        $this->actingAs($normalUser)
            ->putJson("/users/{$target->id}", ['name' => 'Hacked'])
            ->assertStatus(403);
    });

    test('update fails validation when email is invalid', function () {
        $superAdmin = $this->superAdminUser();

        $user = User::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/users/{$user->id}", ['email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('admin cannot update a super admin', function () {
        $admin = $this->adminUser();
        $superAdmin = $this->superAdminUser();

        $this->actingAs($admin)
            ->putJson("/users/{$superAdmin->id}", ['name' => 'Blocked'])
            ->assertStatus(403);
    });

    test('super admin can update an admin', function () {
        $superAdmin = $this->superAdminUser();
        $admin = $this->adminUser();

        $this->actingAs($superAdmin)
            ->putJson("/users/{$admin->id}", ['name' => 'Updated'])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated']);
    });

    test('nullable meta field can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();

        $user = User::factory()->create([
            'meta' => ['department' => 'Engineering'],
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/users/{$user->id}", [
                'meta' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'meta' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.co.uk',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/users/{$user->id}", [
                'role' => 'admin',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Original Name',
            'email' => 'original@example.co.uk',
            'role' => 'admin',
        ]);
    });

    test('patch verb can clear nullable meta field', function () {
        $superAdmin = $this->superAdminUser();

        $user = User::factory()->create([
            'meta' => ['department' => 'Engineering'],
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/users/{$user->id}", [
                'meta' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'meta' => null,
        ]);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a user', function () {
        $superAdmin = $this->superAdminUser();

        $user = User::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/users/{$user->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    });

    test('user without permission cannot soft delete a user', function () {
        $normalUser = $this->normalUser();
        $target = User::factory()->create();

        $this->actingAs($normalUser)
            ->deleteJson("/users/{$target->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for non-existent user', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/users/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted user', function () {
        $superAdmin = $this->superAdminUser();

        $user = User::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/users/{$user->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a user', function () {
        $normalUser = $this->normalUser();
        $target = User::factory()->deleted()->create();

        $this->actingAs($normalUser)
            ->postJson("/users/{$target->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a user that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $user = User::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/users/{$user->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a user', function () {
        $superAdmin = $this->superAdminUser();

        $user = User::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/users/{$user->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    });

    test('user without permission cannot force delete a user', function () {
        $normalUser = $this->normalUser();
        $target = User::factory()->deleted()->create();

        $this->actingAs($normalUser)
            ->deleteJson("/users/{$target->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a user that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $user = User::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/users/{$user->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete users', function () {
        $superAdmin = $this->superAdminUser();

        $users = User::factory()->count(3)->create();
        $ids = $users->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/users/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('users', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/users/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/users/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete users', function () {
        $normalUser = $this->normalUser();
        $users = User::factory()->count(2)->create();

        $this->actingAs($normalUser)
            ->postJson('/users/bulk/delete', [
                'ids' => $users->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore users', function () {
        $superAdmin = $this->superAdminUser();

        $users = User::factory()->count(3)->deleted()->create();
        $ids = $users->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/users/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('users', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/users/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/users/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore users', function () {
        $normalUser = $this->normalUser();
        $users = User::factory()->count(2)->deleted()->create();

        $this->actingAs($normalUser)
            ->postJson('/users/bulk/restore', [
                'ids' => $users->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted users', function () {
        $superAdmin = $this->superAdminUser();

        User::factory()->count(2)->create();
        $trashed = User::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/users')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Index')
                ->has('users')
            );

        $this->assertSoftDeleted('users', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted user', function () {
        $superAdmin = $this->superAdminUser();

        $user = User::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/users/{$user->id}")
            ->assertStatus(404);
    });
});

describe('role hierarchy', function () {
    test('admin cannot view a super admin', function () {
        $admin = $this->adminUser();
        $superAdmin = $this->superAdminUser();

        $this->actingAs($admin)
            ->get("/users/{$superAdmin->id}")
            ->assertStatus(403);
    });

    test('admin cannot edit a super admin', function () {
        $admin = $this->adminUser();
        $superAdmin = $this->superAdminUser();

        $this->actingAs($admin)
            ->get("/users/{$superAdmin->id}/edit")
            ->assertStatus(403);
    });

    test('admin cannot delete a super admin', function () {
        $admin = $this->adminUser();
        $superAdmin = $this->superAdminUser();

        $this->actingAs($admin)
            ->deleteJson("/users/{$superAdmin->id}")
            ->assertStatus(403);
    });

    test('admin cannot force delete a super admin', function () {
        $admin = $this->adminUser();
        $superAdmin = $this->superAdminUser();

        $superAdmin->delete();

        $this->actingAs($admin)
            ->deleteJson("/users/{$superAdmin->id}/force")
            ->assertStatus(403);
    });

    test('admin cannot restore a super admin', function () {
        $admin = $this->adminUser();
        $superAdmin = $this->superAdminUser();

        $superAdmin->delete();

        $this->actingAs($admin)
            ->postJson("/users/{$superAdmin->id}/restore")
            ->assertStatus(403);
    });

    test('admin cannot bulk delete when ids include a super admin', function () {
        $admin = $this->adminUser();
        $superAdmin = $this->superAdminUser();

        $this->actingAs($admin)
            ->postJson('/users/bulk/delete', ['ids' => [$superAdmin->id]])
            ->assertStatus(403);
    });

    test('admin cannot bulk restore when ids include a super admin', function () {
        $admin = $this->adminUser();
        $superAdmin = $this->superAdminUser();

        $superAdmin->delete();

        $this->actingAs($admin)
            ->postJson('/users/bulk/restore', ['ids' => [$superAdmin->id]])
            ->assertStatus(403);
    });

    test('regular user cannot access any user management action', function () {
        $normalUser = $this->normalUser();
        $target = User::factory()->create();

        $this->actingAs($normalUser)->get('/users')->assertStatus(403);
        $this->actingAs($normalUser)->get('/users/create')->assertStatus(403);
        $this->actingAs($normalUser)->get("/users/{$target->id}")->assertStatus(403);
        $this->actingAs($normalUser)->get("/users/{$target->id}/edit")->assertStatus(403);
        $this->actingAs($normalUser)->putJson("/users/{$target->id}", ['name' => 'X'])->assertStatus(403);
        $this->actingAs($normalUser)->deleteJson("/users/{$target->id}")->assertStatus(403);
    });
});

<?php

use App\Models\Category;
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
    test('authenticated user with permission can list categories', function () {
        $superAdmin = $this->superAdminUser();

        Category::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/categories')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Categories/Index')
                ->has('categories')
            );
    });

    test('unauthenticated user cannot list categories', function () {
        $this->get('/categories')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list categories', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/categories')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/categories/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Categories/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/categories/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/categories/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a category', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'name' => 'Engineering',
            'slug' => 'engineering',
            'description' => 'Posts related to engineering topics.',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/categories', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Engineering']);

        $this->assertDatabaseHas('categories', [
            'name' => 'Engineering',
            'slug' => 'engineering',
        ]);
    });

    test('authenticated user with permission can create a child category', function () {
        $superAdmin = $this->superAdminUser();

        $parent = Category::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/categories', [
                'name' => 'Software Engineering',
                'slug' => 'software-engineering',
                'parent_id' => $parent->id,
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['parent_id' => $parent->id]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Software Engineering',
            'parent_id' => $parent->id,
        ]);
    });

    test('user without permission cannot create a category', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/categories', [
                'name' => 'Blocked Category',
                'slug' => 'blocked-category',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when name is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/categories', [
                'slug' => 'no-name',
                'description' => 'No name here.',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('store auto-generates a slug when slug is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/categories', [
                'name' => 'No Slug',
            ])
            ->assertStatus(201)
            ->assertJsonPath('slug', 'no-slug');

        $this->assertDatabaseHas('categories', [
            'name' => 'No Slug',
            'slug' => 'no-slug',
        ]);
    });

    test('store fails validation when slug already exists', function () {
        $superAdmin = $this->superAdminUser();

        Category::factory()->create(['slug' => 'duplicate-slug']);

        $this->actingAs($superAdmin)
            ->postJson('/categories', [
                'name' => 'Duplicate Slug',
                'slug' => 'duplicate-slug',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    });

    test('store fails validation when parent_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/categories', [
                'name' => 'Invalid Parent',
                'slug' => 'invalid-parent',
                'parent_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/categories', [
                'name' => 'Minimal Category',
                'slug' => 'minimal-category',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('categories', [
            'name' => 'Minimal Category',
            'slug' => 'minimal-category',
            'parent_id' => null,
            'description' => null,
        ]);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a category', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/categories/{$category->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Categories/Show')
                ->has('category')
            );
    });

    test('unauthenticated user cannot view a category', function () {
        $category = Category::factory()->create();

        $this->get("/categories/{$category->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a category', function () {
        $user = $this->userWithNoPermissions();

        $category = Category::factory()->create();

        $this->actingAs($user)
            ->get("/categories/{$category->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent category', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/categories/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/categories/{$category->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Categories/Edit')
                ->has('category')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $category = Category::factory()->create();

        $this->get("/categories/{$category->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $category = Category::factory()->create();

        $this->actingAs($user)
            ->get("/categories/{$category->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a category', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->create(['name' => 'Old Name']);

        $this->actingAs($superAdmin)
            ->putJson("/categories/{$category->id}", [
                'name' => 'New Name',
                'slug' => $category->slug,
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Name',
        ]);
    });

    test('patch verb also updates a category', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->create(['description' => 'Old description']);

        $this->actingAs($superAdmin)
            ->patchJson("/categories/{$category->id}", [
                'slug' => $category->slug,
                'description' => 'New description',
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['description' => 'New description']);
    });

    test('user without permission cannot update a category', function () {
        $user = $this->normalUser();

        $category = Category::factory()->create();

        $this->actingAs($user)
            ->putJson("/categories/{$category->id}", [
                'name' => 'New Name',
                'slug' => $category->slug,
            ])
            ->assertStatus(403);
    });

    test('update fails validation when slug already exists on another category', function () {
        $superAdmin = $this->superAdminUser();

        Category::factory()->create(['slug' => 'taken-slug']);
        $category = Category::factory()->create(['slug' => 'free-slug']);

        $this->actingAs($superAdmin)
            ->putJson("/categories/{$category->id}", ['slug' => 'taken-slug'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    });

    test('update allows a category to keep its own slug', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->create(['slug' => 'keep-slug']);

        $this->actingAs($superAdmin)
            ->putJson("/categories/{$category->id}", [
                'slug' => 'keep-slug',
                'name' => 'Renamed Category',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'slug' => 'keep-slug',
            'name' => 'Renamed Category',
        ]);
    });

    test('update fails validation when parent_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/categories/{$category->id}", [
                'slug' => $category->slug,
                'parent_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    });

    test('update fails validation when slug is set to null', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/categories/{$category->id}", [
                'slug' => null,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    });

    test('nullable fields can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();
        $parent = Category::factory()->create();

        $category = Category::factory()->create([
            'description' => 'Some description.',
            'parent_id' => $parent->id,
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/categories/{$category->id}", [
                'slug' => $category->slug,
                'description' => null,
                'parent_id' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'description' => null,
            'parent_id' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->create([
            'description' => 'Original description.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/categories/{$category->id}", [
                'slug' => $category->slug,
                'name' => 'Updated Name',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'description' => 'Original description.',
        ]);
    });

    test('patch verb can clear nullable description field', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->create([
            'description' => 'Some description.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/categories/{$category->id}", [
                'slug' => $category->slug,
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'description' => null,
        ]);
    });

    test('parent_id can be updated to associate a category with a different parent', function () {
        $superAdmin = $this->superAdminUser();
        $newParent = Category::factory()->create();

        $category = Category::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/categories/{$category->id}", [
                'slug' => $category->slug,
                'parent_id' => $newParent->id,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'parent_id' => $newParent->id,
        ]);
    });

    test('a category cannot be set as its own parent', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/categories/{$category->id}", [
                'slug' => $category->slug,
                'parent_id' => $category->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    });

    test('logs category updates with actor id', function () {
        $actor = $this->adminUser();

        $category = Category::factory()->create(['name' => 'Old Name']);

        $this->actingAs($actor)
            ->putJson("/categories/{$category->id}", [
                'name' => 'New Name',
                'slug' => $category->slug,
            ])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_CATEGORY)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a category', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/categories/{$category->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    });

    test('user without permission cannot soft delete a category', function () {
        $user = $this->normalUser();

        $category = Category::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/categories/{$category->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent category', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/categories/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted category', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/categories/{$category->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a category', function () {
        $user = $this->normalUser();

        $category = Category::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/categories/{$category->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a category that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/categories/{$category->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a category', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/categories/{$category->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    });

    test('user without permission cannot force delete a category', function () {
        $user = $this->normalUser();

        $category = Category::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/categories/{$category->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a category that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/categories/{$category->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete categories', function () {
        $superAdmin = $this->superAdminUser();

        $categories = Category::factory()->count(3)->create();
        $ids = $categories->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/categories/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('categories', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/categories/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/categories/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete categories', function () {
        $user = $this->normalUser();

        $categories = Category::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/categories/bulk/delete', [
                'ids' => $categories->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore categories', function () {
        $superAdmin = $this->superAdminUser();

        $categories = Category::factory()->count(3)->deleted()->create();
        $ids = $categories->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/categories/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('categories', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/categories/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/categories/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore categories', function () {
        $user = $this->normalUser();

        $categories = Category::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/categories/bulk/restore', [
                'ids' => $categories->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted categories', function () {
        $superAdmin = $this->superAdminUser();

        Category::factory()->count(2)->create();
        $trashed = Category::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/categories')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Categories/Index')
                ->has('categories')
            );

        $this->assertSoftDeleted('categories', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted category', function () {
        $superAdmin = $this->superAdminUser();

        $category = Category::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/categories/{$category->id}")
            ->assertStatus(404);
    });
});

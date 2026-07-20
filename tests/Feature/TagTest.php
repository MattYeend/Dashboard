<?php

use App\Models\Log;
use App\Models\Tag;
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
    test('authenticated user with permission can list tags', function () {
        $superAdmin = $this->superAdminUser();

        Tag::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/tags')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tags/Index')
                ->has('tags')
            );
    });

    test('unauthenticated user cannot list tags', function () {
        $this->get('/tags')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list tags', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/tags')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/tags/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tags/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/tags/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/tags/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a tag', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tags', ['name' => 'Laravel Tips'])
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Laravel Tips']);

        $this->assertDatabaseHas('tags', [
            'name' => 'Laravel Tips',
            'slug' => 'laravel-tips',
        ]);
    });

    test('user without permission cannot create a tag', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/tags', ['name' => 'Blocked Tag'])
            ->assertStatus(403);
    });

    test('store fails validation when name is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tags', ['meta' => ['colour' => 'blue']])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('store fails validation when slug already exists', function () {
        $superAdmin = $this->superAdminUser();

        Tag::factory()->create(['slug' => 'duplicate-slug']);

        $this->actingAs($superAdmin)
            ->postJson('/tags', [
                'name' => 'Duplicate Slug',
                'slug' => 'duplicate-slug',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    });

    test('store fails validation when slug format is invalid', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tags', [
                'name' => 'Bad Slug Tag',
                'slug' => 'Not A Valid Slug!',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tags', ['name' => 'Minimal Tag'])
            ->assertStatus(201);

        $this->assertDatabaseHas('tags', [
            'name' => 'Minimal Tag',
            'slug' => 'minimal-tag',
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tags', [
                'name' => 'Tag With Meta',
                'meta' => ['colour' => 'blue', 'featured' => true],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('tags', ['name' => 'Tag With Meta']);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a tag', function () {
        $superAdmin = $this->superAdminUser();

        $tag = Tag::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/tags/{$tag->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tags/Show')
                ->has('tag')
            );
    });

    test('unauthenticated user cannot view a tag', function () {
        $tag = Tag::factory()->create();

        $this->get("/tags/{$tag->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a tag', function () {
        $user = $this->normalUser();

        $tag = Tag::factory()->create();

        $this->actingAs($user)
            ->get("/tags/{$tag->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent tag', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/tags/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $tag = Tag::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/tags/{$tag->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tags/Edit')
                ->has('tag')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $tag = Tag::factory()->create();

        $this->get("/tags/{$tag->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $tag = Tag::factory()->create();

        $this->actingAs($user)
            ->get("/tags/{$tag->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a tag', function () {
        $superAdmin = $this->superAdminUser();

        $tag = Tag::factory()->create(['name' => 'Old Name']);

        $this->actingAs($superAdmin)
            ->putJson("/tags/{$tag->id}", ['name' => 'New Name'])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'New Name',
        ]);
    });

    test('patch verb also updates a tag', function () {
        $superAdmin = $this->superAdminUser();

        $tag = Tag::factory()->create(['name' => 'Old Name']);

        $this->actingAs($superAdmin)
            ->patchJson("/tags/{$tag->id}", ['name' => 'Patched Name'])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Patched Name']);
    });

    test('user without permission cannot update a tag', function () {
        $user = $this->normalUser();

        $tag = Tag::factory()->create();

        $this->actingAs($user)
            ->putJson("/tags/{$tag->id}", ['name' => 'New Name'])
            ->assertStatus(403);
    });

    test('update fails validation when slug already exists on another tag', function () {
        $superAdmin = $this->superAdminUser();

        Tag::factory()->create(['slug' => 'taken-slug']);
        $tag = Tag::factory()->create(['slug' => 'free-slug']);

        $this->actingAs($superAdmin)
            ->putJson("/tags/{$tag->id}", ['slug' => 'taken-slug'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    });

    test('update allows a tag to keep its own slug', function () {
        $superAdmin = $this->superAdminUser();

        $tag = Tag::factory()->create(['slug' => 'keep-slug']);

        $this->actingAs($superAdmin)
            ->putJson("/tags/{$tag->id}", [
                'slug' => 'keep-slug',
                'name' => 'Renamed Tag',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'slug' => 'keep-slug',
            'name' => 'Renamed Tag',
        ]);
    });

    test('renaming a tag without an explicit slug regenerates a unique slug', function () {
        $superAdmin = $this->superAdminUser();

        $tag = Tag::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $this->actingAs($superAdmin)
            ->putJson("/tags/{$tag->id}", ['name' => 'Fresh Name'])
            ->assertStatus(200);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Fresh Name',
            'slug' => 'fresh-name',
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $tag = Tag::factory()->create([
            'meta' => ['colour' => 'blue'],
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/tags/{$tag->id}", ['name' => 'Updated Name'])
            ->assertStatus(200);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Updated Name',
        ]);

        expect($tag->fresh()->meta)->toBe(['colour' => 'blue']);
    });

    test('patch verb can clear nullable meta', function () {
        $superAdmin = $this->superAdminUser();

        $tag = Tag::factory()->create(['meta' => ['colour' => 'blue']]);

        $this->actingAs($superAdmin)
            ->patchJson("/tags/{$tag->id}", ['meta' => null])
            ->assertStatus(200);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'meta' => null,
        ]);
    });

    test('logs tag updates with actor id', function () {
        $actor = $this->adminUser();

        $tag = Tag::factory()->create(['name' => 'Old Name']);

        $this->actingAs($actor)
            ->putJson("/tags/{$tag->id}", ['name' => 'New Name'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_TAG)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a tag', function () {
        $superAdmin = $this->superAdminUser();

        $tag = Tag::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/tags/{$tag->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('tags', ['id' => $tag->id]);
    });

    test('user without permission cannot soft delete a tag', function () {
        $user = $this->normalUser();

        $tag = Tag::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/tags/{$tag->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent tag', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/tags/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted tag', function () {
        $superAdmin = $this->superAdminUser();

        $tag = Tag::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/tags/{$tag->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a tag', function () {
        $user = $this->normalUser();

        $tag = Tag::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/tags/{$tag->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a tag that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $tag = Tag::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/tags/{$tag->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a tag', function () {
        $superAdmin = $this->superAdminUser();

        $tag = Tag::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/tags/{$tag->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    });

    test('user without permission cannot force delete a tag', function () {
        $user = $this->normalUser();

        $tag = Tag::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/tags/{$tag->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a tag that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $tag = Tag::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/tags/{$tag->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete tags', function () {
        $superAdmin = $this->superAdminUser();

        $tags = Tag::factory()->count(3)->create();
        $ids = $tags->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/tags/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('tags', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tags/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tags/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete tags', function () {
        $user = $this->normalUser();

        $tags = Tag::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/tags/bulk/delete', [
                'ids' => $tags->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore tags', function () {
        $superAdmin = $this->superAdminUser();

        $tags = Tag::factory()->count(3)->deleted()->create();
        $ids = $tags->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/tags/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('tags', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tags/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/tags/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore tags', function () {
        $user = $this->normalUser();

        $tags = Tag::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/tags/bulk/restore', [
                'ids' => $tags->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted tags', function () {
        $superAdmin = $this->superAdminUser();

        Tag::factory()->count(2)->create();
        $trashed = Tag::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/tags')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tags/Index')
                ->has('tags')
            );

        $this->assertSoftDeleted('tags', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted tag', function () {
        $superAdmin = $this->superAdminUser();

        $tag = Tag::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/tags/{$tag->id}")
            ->assertStatus(404);
    });
});

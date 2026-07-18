<?php

use App\Models\Category;
use App\Models\Log;
use App\Models\Post;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
    test('authenticated user with permission can list posts', function () {
        $superAdmin = $this->superAdminUser();

        Post::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/posts')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Posts/Index')
                ->has('posts')
            );
    });

    test('unauthenticated user cannot list posts', function () {
        $this->get('/posts')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list posts', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/posts')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/posts/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Posts/Create')
                ->has('categories')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/posts/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/posts/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a post', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'title' => 'My First Post',
            'description' => 'This is the post description.',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/posts', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'My First Post']);

        $this->assertDatabaseHas('posts', [
            'title' => 'My First Post',
            'description' => '<p>This is the post description.</p>',
        ]);
    });

    test('user without permission cannot create a post', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/posts', [
                'title' => 'Blocked Post',
                'description' => 'Should not be created.',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/posts', [
                'description' => 'No title here.',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when description is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/posts', [
                'title' => 'No Description',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    });

    test('store fails validation when title exceeds max length', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/posts', [
                'title' => str_repeat('a', 256),
                'description' => 'Valid description.',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/posts', [
                'title' => 'Post with meta',
                'description' => 'Has some meta data attached.',
                'meta' => ['featured' => true, 'tags' => ['news']],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('posts', ['title' => 'Post with meta']);
    });

    test('store succeeds with an uploaded image', function () {
        Storage::fake('public');

        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->post('/posts', [
                'title' => 'Post with image',
                'description' => 'Has an uploaded image.',
                'image' => UploadedFile::fake()->image('post.jpg'),
            ])
            ->assertRedirect();

        $post = Post::where('title', 'Post with image')->firstOrFail();

        expect($post->image)->not->toBeNull();

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $disk->assertExists($post->image);
    });

    test('store fails validation when image is not a valid image file', function () {
        Storage::fake('public');

        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->post('/posts', [
                'title' => 'Post with bad file',
                'description' => 'Has an invalid file.',
                'image' => UploadedFile::fake()->create('document.pdf', 100),
            ])
            ->assertSessionHasErrors(['image']);
    });

    test('store attaches selected categories', function () {
        $superAdmin = $this->superAdminUser();

        $categories = Category::factory()->count(2)->create();

        $response = $this->actingAs($superAdmin)
            ->postJson('/posts', [
                'title' => 'Categorised Post',
                'description' => 'Belongs to categories.',
                'category_ids' => $categories->pluck('id')->all(),
            ])
            ->assertStatus(201);

        $post = Post::where('title', 'Categorised Post')->firstOrFail();

        expect($post->categories()->pluck('categories.id')->sort()->values()->all())
            ->toBe($categories->pluck('id')->sort()->values()->all());
    });

    test('store fails validation when category_ids contains a non-existent id', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/posts', [
                'title' => 'Invalid Category Post',
                'description' => 'Should fail validation.',
                'category_ids' => [99999],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category_ids.0']);
    });

    test('store succeeds without any categories', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/posts', [
                'title' => 'Uncategorised Post',
                'description' => 'No categories attached.',
            ])
            ->assertStatus(201);

        $post = Post::where('title', 'Uncategorised Post')->firstOrFail();

        expect($post->categories()->count())->toBe(0);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a post', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/posts/{$post->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Posts/Show')
                ->has('post')
            );
    });

    test('unauthenticated user cannot view a post', function () {
        $post = Post::factory()->create();

        $this->get("/posts/{$post->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a post', function () {
        $user = $this->normalUser();

        $post = Post::factory()->create();

        $this->actingAs($user)
            ->get("/posts/{$post->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent post', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/posts/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/posts/{$post->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Posts/Edit')
                ->has('post')
                ->has('categories')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $post = Post::factory()->create();

        $this->get("/posts/{$post->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $post = Post::factory()->create();

        $this->actingAs($user)
            ->get("/posts/{$post->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a post', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->create(['title' => 'Old Title']);

        $this->actingAs($superAdmin)
            ->putJson("/posts/{$post->id}", [
                'title' => 'New Title',
                'description' => $post->description,
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'New Title']);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'New Title',
        ]);
    });

    test('patch verb also updates a post', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->create(['description' => 'Old description']);

        $this->actingAs($superAdmin)
            ->patchJson("/posts/{$post->id}", [
                'description' => 'New description',
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['description' => '<p>New description</p>']);
    });

    test('user without permission cannot update a post', function () {
        $user = $this->normalUser();

        $post = Post::factory()->create();

        $this->actingAs($user)
            ->putJson("/posts/{$post->id}", ['title' => 'New Title'])
            ->assertStatus(403);
    });

    test('update fails validation when description is set to null', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/posts/{$post->id}", [
                'description' => null,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->create([
            'title' => 'Original title',
            'description' => 'Original description.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/posts/{$post->id}", [
                'title' => 'Updated title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated title',
            'description' => 'Original description.',
        ]);
    });

    test('update syncs categories when category_ids is provided', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->create();
        $original = Category::factory()->create();
        $post->categories()->sync([$original->id]);

        $replacement = Category::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/posts/{$post->id}", [
                'title' => $post->title,
                'description' => $post->description,
                'category_ids' => [$replacement->id],
            ])
            ->assertStatus(200);

        expect($post->categories()->pluck('categories.id')->all())
            ->toBe([$replacement->id]);
    });

    test('update does not clear categories when category_ids is omitted', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->create();
        $category = Category::factory()->create();
        $post->categories()->sync([$category->id]);

        $this->actingAs($superAdmin)
            ->patchJson("/posts/{$post->id}", [
                'title' => 'Title only update',
            ])
            ->assertStatus(200);

        expect($post->categories()->pluck('categories.id')->all())
            ->toBe([$category->id]);
    });

    test('update clears categories when an empty category_ids array is provided', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->create();
        $category = Category::factory()->create();
        $post->categories()->sync([$category->id]);

        $this->actingAs($superAdmin)
            ->putJson("/posts/{$post->id}", [
                'title' => $post->title,
                'description' => $post->description,
                'category_ids' => [],
            ])
            ->assertStatus(200);

        expect($post->categories()->count())->toBe(0);
    });

    test('update fails validation when category_ids contains a non-existent id', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/posts/{$post->id}", [
                'title' => $post->title,
                'description' => $post->description,
                'category_ids' => [99999],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category_ids.0']);
    });

    test('logs post updates with actor id', function () {
        $actor = $this->adminUser();

        $post = Post::factory()->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/posts/{$post->id}", [
                'title' => 'New Title',
                'description' => $post->description,
            ])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_POST)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a post', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/posts/{$post->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    });

    test('user without permission cannot soft delete a post', function () {
        $user = $this->normalUser();

        $post = Post::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/posts/{$post->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent post', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/posts/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted post', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/posts/{$post->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a post', function () {
        $user = $this->normalUser();

        $post = Post::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/posts/{$post->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a post that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/posts/{$post->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a post', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/posts/{$post->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    });

    test('user without permission cannot force delete a post', function () {
        $user = $this->normalUser();

        $post = Post::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/posts/{$post->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a post that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/posts/{$post->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete posts', function () {
        $superAdmin = $this->superAdminUser();

        $posts = Post::factory()->count(3)->create();
        $ids = $posts->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/posts/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('posts', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/posts/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/posts/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete posts', function () {
        $user = $this->normalUser();

        $posts = Post::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/posts/bulk/delete', [
                'ids' => $posts->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore posts', function () {
        $superAdmin = $this->superAdminUser();

        $posts = Post::factory()->count(3)->deleted()->create();
        $ids = $posts->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/posts/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('posts', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/posts/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/posts/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore posts', function () {
        $user = $this->normalUser();

        $posts = Post::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/posts/bulk/restore', [
                'ids' => $posts->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted posts', function () {
        $superAdmin = $this->superAdminUser();

        Post::factory()->count(2)->create();
        $trashed = Post::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/posts')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Posts/Index')
                ->has('posts')
            );

        $this->assertSoftDeleted('posts', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted post', function () {
        $superAdmin = $this->superAdminUser();

        $post = Post::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/posts/{$post->id}")
            ->assertStatus(404);
    });
});

<?php

use App\Models\Comment;
use App\Models\Log;
use App\Models\Post;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
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

describe('store', function () {
    test('authenticated admin can comment on a post', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);

        $this->actingAs($admin)
            ->postJson(route('posts.comments.store', $post), [
                'content' => 'This is a test comment.',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'content' => '<p>This is a test comment.</p>',
            'created_by' => $admin->id,
        ]);
    });

    test('unauthenticated user cannot comment on a post', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);

        $this->postJson(route('posts.comments.store', $post), [
            'content' => 'This is a test comment.',
        ])->assertStatus(401);

        $this->assertDatabaseCount('comments', 0);
    });

    test('user without permission cannot comment on a post', function () {
        $admin = $this->adminUser();
        $user = $this->normalUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);

        $this->actingAs($user)
            ->postJson(route('posts.comments.store', $post), [
                'content' => 'This is a test comment.',
            ])
            ->assertStatus(403);

        $this->assertDatabaseCount('comments', 0);
    });

    test('store fails validation when content is missing', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);

        $this->actingAs($admin)
            ->postJson(route('posts.comments.store', $post), [
                'content' => '',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    });

    test('store fails validation when content exceeds 2000 characters', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);

        $this->actingAs($admin)
            ->postJson(route('posts.comments.store', $post), [
                'content' => str_repeat('a', 2001),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    });

    test('logs comment creation with actor id', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);

        $this->actingAs($admin)
            ->postJson(route('posts.comments.store', $post), [
                'content' => 'Logged comment.',
            ])
            ->assertStatus(201);

        $log = Log::query()
            ->where('action_id', Log::ACTION_CREATE_COMMENT)
            ->where('logged_in_user_id', $admin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['after']);
    });
});

describe('update', function () {
    test('author can update their own comment', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->putJson(route('posts.comments.update', [$post, $comment]), [
                'content' => 'Updated comment content.',
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['content' => '<p>Updated comment content.</p>']);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => '<p>Updated comment content.</p>',
            'updated_by' => $admin->id,
        ]);
    });

    test('another admin cannot update someone else\'s comment', function () {
        $author = $this->adminUser();
        $otherAdmin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $author->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $author->id,
        ]);

        $this->actingAs($otherAdmin)
            ->putJson(route('posts.comments.update', [$post, $comment]), [
                'content' => 'Attempted edit.',
            ])
            ->assertStatus(403);
    });

    test('unauthenticated user cannot update a comment', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $admin->id,
        ]);

        $this->putJson(route('posts.comments.update', [$post, $comment]), [
            'content' => 'Attempted edit.',
        ])->assertStatus(401);
    });

    test('update fails validation when content is missing', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->putJson(route('posts.comments.update', [$post, $comment]), [
                'content' => '',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    });

    test('logs comment updates with actor id', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->putJson(route('posts.comments.update', [$post, $comment]), [
                'content' => 'Updated for logging.',
            ])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_COMMENT)
            ->where('logged_in_user_id', $admin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('author can delete their own comment', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->deleteJson(route('posts.comments.destroy', [$post, $comment]))
            ->assertStatus(204);

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    });

    test('a normal user cannot delete someone else\'s comment', function () {
        $admin = $this->adminUser();
        $user = $this->normalUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($user)
            ->deleteJson(route('posts.comments.destroy', [$post, $comment]))
            ->assertStatus(403);

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'deleted_at' => null]);
    });

    test('an admin can delete another admin\'s comment (moderation)', function () {
        $author = $this->adminUser();
        $moderator = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $author->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $author->id,
        ]);

        $this->actingAs($moderator)
            ->deleteJson(route('posts.comments.destroy', [$post, $comment]))
            ->assertStatus(204);

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    });

    test('an admin cannot delete a comment created by a super admin', function () {
        $superAdmin = $this->superAdminUser();
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $superAdmin->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $superAdmin->id,
        ]);

        $this->actingAs($admin)
            ->deleteJson(route('posts.comments.destroy', [$post, $comment]))
            ->assertStatus(403);

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'deleted_at' => null]);
    });

    test('destroy returns 404 for a non-existent comment', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);

        $this->actingAs($admin)
            ->deleteJson(route('posts.comments.destroy', [$post, 99999]))
            ->assertStatus(404);
    });

    test('logs comment deletion with actor id', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->deleteJson(route('posts.comments.destroy', [$post, $comment]))
            ->assertStatus(204);

        $log = Log::query()
            ->where('action_id', Log::ACTION_DELETE_COMMENT)
            ->where('logged_in_user_id', $admin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before']);
    });
});

describe('like', function () {
    test('authenticated admin can like a comment', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('posts.comments.like', [$post, $comment]))
            ->assertRedirect();

        $this->assertDatabaseHas('likes', [
            'likeable_id' => $comment->id,
            'likeable_type' => $comment->getMorphClass(),
            'user_id' => $admin->id,
        ]);
    });

    test('unauthenticated user cannot like a comment', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $admin->id,
        ]);

        $this->post(route('posts.comments.like', [$post, $comment]))
            ->assertRedirect('/login');

        $this->assertDatabaseCount('likes', 0);
    });

    test('user without permission cannot like a comment', function () {
        $admin = $this->adminUser();
        $user = $this->normalUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($user)
            ->post(route('posts.comments.like', [$post, $comment]))
            ->assertStatus(403);

        $this->assertDatabaseCount('likes', 0);
    });

    test('liking the same comment twice does not create duplicate likes', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('posts.comments.like', [$post, $comment]));
        $this->actingAs($admin)->post(route('posts.comments.like', [$post, $comment]));

        $this->assertDatabaseCount('likes', 1);
    });
});

describe('unlike', function () {
    test('a user can unlike a comment they previously liked', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('posts.comments.like', [$post, $comment]));

        $this->actingAs($admin)
            ->delete(route('posts.comments.unlike', [$post, $comment]))
            ->assertRedirect();

        $this->assertDatabaseCount('likes', 0);
    });

    test('unauthenticated user cannot unlike a comment', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $admin->id,
        ]);

        $this->delete(route('posts.comments.unlike', [$post, $comment]))
            ->assertRedirect('/login');
    });

    test('unliking a comment that was not liked is a no-op', function () {
        $admin = $this->adminUser();

        $post = Post::factory()->create(['created_by' => $admin->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('posts.comments.unlike', [$post, $comment]))
            ->assertRedirect();

        $this->assertDatabaseCount('likes', 0);
    });
});

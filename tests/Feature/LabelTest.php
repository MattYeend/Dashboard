<?php

use App\Models\Label;
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

describe('index', function () {
    test('authenticated user with permission can list labels', function () {
        $superAdmin = $this->superAdminUser();

        Label::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/labels')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Labels/Index')
                ->has('labels')
            );
    });

    test('unauthenticated user cannot list labels', function () {
        $this->get('/labels')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list labels', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/labels')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/labels/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Labels/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/labels/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/labels/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a label', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'name' => 'High Priority',
            'slug' => 'high-priority',
            'background_colour' => '#b91c1c',
            'text_colour' => '#ffffff',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/labels', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'High Priority']);

        $this->assertDatabaseHas('labels', [
            'name' => 'High Priority',
            'slug' => 'high-priority',
            'background_colour' => '#b91c1c',
            'text_colour' => '#ffffff',
        ]);
    });

    test('user without permission cannot create a label', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/labels', [
                'name' => 'Blocked Label',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when name is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/labels', [
                'slug' => 'no-name',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('store auto-generates a slug when slug is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/labels', [
                'name' => 'No Slug',
            ])
            ->assertStatus(201)
            ->assertJsonPath('slug', 'no-slug');

        $this->assertDatabaseHas('labels', [
            'name' => 'No Slug',
            'slug' => 'no-slug',
        ]);
    });

    test('store fails validation when slug already exists', function () {
        $superAdmin = $this->superAdminUser();

        Label::factory()->create(['slug' => 'duplicate-slug']);

        $this->actingAs($superAdmin)
            ->postJson('/labels', [
                'name' => 'Duplicate Slug',
                'slug' => 'duplicate-slug',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    });

    test('store fails validation when slug is not a valid slug format', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/labels', [
                'name' => 'Bad Slug Format',
                'slug' => 'Not A Valid Slug!',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    });

    test('store fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/labels', [
                'name' => 'Invalid Background',
                'background_colour' => 'not-a-colour',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('store fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/labels', [
                'name' => 'Invalid Text Colour',
                'text_colour' => 'not-a-colour',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/labels', [
                'name' => 'Minimal Label',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('labels', [
            'name' => 'Minimal Label',
            'slug' => 'minimal-label',
            'background_colour' => '#6b7280',
            'text_colour' => '#ffffff',
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/labels', [
                'name' => 'Label With Meta',
                'meta' => ['icon' => 'flag'],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('labels', ['name' => 'Label With Meta']);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a label', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/labels/{$label->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Labels/Show')
                ->has('label')
            );
    });

    test('unauthenticated user cannot view a label', function () {
        $label = Label::factory()->create();

        $this->get("/labels/{$label->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a label', function () {
        $user = $this->userWithNoPermissions();

        $label = Label::factory()->create();

        $this->actingAs($user)
            ->get("/labels/{$label->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent label', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/labels/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/labels/{$label->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Labels/Edit')
                ->has('label')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $label = Label::factory()->create();

        $this->get("/labels/{$label->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $label = Label::factory()->create();

        $this->actingAs($user)
            ->get("/labels/{$label->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a label', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->create(['name' => 'Old Name']);

        $this->actingAs($superAdmin)
            ->putJson("/labels/{$label->id}", [
                'name' => 'New Name',
                'slug' => $label->slug,
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);

        $this->assertDatabaseHas('labels', [
            'id' => $label->id,
            'name' => 'New Name',
        ]);
    });

    test('patch verb also updates a label', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->create(['background_colour' => '#ffffff']);

        $this->actingAs($superAdmin)
            ->patchJson("/labels/{$label->id}", [
                'slug' => $label->slug,
                'background_colour' => '#bee3f8',
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['background_colour' => '#bee3f8']);
    });

    test('user without permission cannot update a label', function () {
        $user = $this->normalUser();

        $label = Label::factory()->create();

        $this->actingAs($user)
            ->putJson("/labels/{$label->id}", [
                'name' => 'New Name',
                'slug' => $label->slug,
            ])
            ->assertStatus(403);
    });

    test('update fails validation when slug already exists on another label', function () {
        $superAdmin = $this->superAdminUser();

        Label::factory()->create(['slug' => 'taken-slug']);
        $label = Label::factory()->create(['slug' => 'free-slug']);

        $this->actingAs($superAdmin)
            ->putJson("/labels/{$label->id}", ['slug' => 'taken-slug'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    });

    test('update allows a label to keep its own slug', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->create(['slug' => 'keep-slug']);

        $this->actingAs($superAdmin)
            ->putJson("/labels/{$label->id}", [
                'slug' => 'keep-slug',
                'name' => 'Renamed Label',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('labels', [
            'id' => $label->id,
            'slug' => 'keep-slug',
            'name' => 'Renamed Label',
        ]);
    });

    test('update fails validation when slug is set to null', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/labels/{$label->id}", [
                'slug' => null,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    });

    test('update fails validation when slug is not a valid slug format', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/labels/{$label->id}", [
                'slug' => 'Not A Valid Slug!',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    });

    test('update fails validation when background_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/labels/{$label->id}", [
                'slug' => $label->slug,
                'background_colour' => 'not-a-colour',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('update fails validation when text_colour is not a valid hex', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/labels/{$label->id}", [
                'slug' => $label->slug,
                'text_colour' => 'not-a-colour',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->create([
            'background_colour' => '#bee3f8',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/labels/{$label->id}", [
                'slug' => $label->slug,
                'name' => 'Updated Name',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('labels', [
            'id' => $label->id,
            'name' => 'Updated Name',
            'background_colour' => '#bee3f8',
        ]);
    });

    test('background_colour and text_colour can be updated together', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->create([
            'background_colour' => '#6b7280',
            'text_colour' => '#ffffff',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/labels/{$label->id}", [
                'slug' => $label->slug,
                'background_colour' => '#dc2626',
                'text_colour' => '#111827',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('labels', [
            'id' => $label->id,
            'background_colour' => '#dc2626',
            'text_colour' => '#111827',
        ]);
    });

    test('logs label updates with actor id', function () {
        $actor = $this->adminUser();

        $label = Label::factory()->create(['name' => 'Old Name']);

        $this->actingAs($actor)
            ->putJson("/labels/{$label->id}", [
                'name' => 'New Name',
                'slug' => $label->slug,
            ])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_LABEL)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a label', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/labels/{$label->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('labels', ['id' => $label->id]);
    });

    test('user without permission cannot soft delete a label', function () {
        $user = $this->normalUser();

        $label = Label::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/labels/{$label->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent label', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/labels/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted label', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/labels/{$label->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('labels', [
            'id' => $label->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a label', function () {
        $user = $this->normalUser();

        $label = Label::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/labels/{$label->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a label that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/labels/{$label->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a label', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/labels/{$label->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('labels', ['id' => $label->id]);
    });

    test('user without permission cannot force delete a label', function () {
        $user = $this->normalUser();

        $label = Label::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/labels/{$label->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a label that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/labels/{$label->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete labels', function () {
        $superAdmin = $this->superAdminUser();

        $labels = Label::factory()->count(3)->create();
        $ids = $labels->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/labels/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('labels', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/labels/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/labels/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete labels', function () {
        $user = $this->normalUser();

        $labels = Label::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/labels/bulk/delete', [
                'ids' => $labels->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore labels', function () {
        $superAdmin = $this->superAdminUser();

        $labels = Label::factory()->count(3)->deleted()->create();
        $ids = $labels->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/labels/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('labels', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/labels/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/labels/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore labels', function () {
        $user = $this->normalUser();

        $labels = Label::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/labels/bulk/restore', [
                'ids' => $labels->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted labels', function () {
        $superAdmin = $this->superAdminUser();

        Label::factory()->count(2)->create();
        $trashed = Label::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/labels')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Labels/Index')
                ->has('labels')
            );

        $this->assertSoftDeleted('labels', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted label', function () {
        $superAdmin = $this->superAdminUser();

        $label = Label::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/labels/{$label->id}")
            ->assertStatus(404);
    });
});
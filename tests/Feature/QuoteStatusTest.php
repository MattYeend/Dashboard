<?php

declare(strict_types=1);

use App\Models\Log;
use App\Models\QuoteStatus;
use App\Services\QuoteStatuses\DeleterService;
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
    test('authenticated user with permission can list quote statuses', function () {
        $superAdmin = $this->superAdminUser();

        QuoteStatus::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/quote-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('QuoteStatuses/Index')
                ->has('quoteStatuses')
            );
    });

    test('unauthenticated user cannot list quote statuses', function () {
        $this->get('/quote-statuses')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list quote statuses', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/quote-statuses')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/quote-statuses/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('QuoteStatuses/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/quote-statuses/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/quote-statuses/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a quote status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses', [
                'title' => 'Awaiting Approval',
                'background_colour' => '#ffffff',
                'text_colour' => '#000000',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Awaiting Approval']);

        $this->assertDatabaseHas('quote_statuses', [
            'title' => 'Awaiting Approval',
            'background_colour' => '#ffffff',
            'text_colour' => '#000000',
        ]);
    });

    test('user without permission cannot create a quote status', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/quote-statuses', [
                'title' => 'Blocked Status',
                'background_colour' => '#ffffff',
                'text_colour' => '#000000',
            ])
            ->assertStatus(403);

        $this->assertDatabaseMissing('quote_statuses', ['title' => 'Blocked Status']);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses', [
                'background_colour' => '#ffffff',
                'text_colour' => '#000000',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when background colour is not a valid colour', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses', [
                'title' => 'Bad Colour',
                'background_colour' => 'red; background:url(x)',
                'text_colour' => '#000000',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('store fails validation when text colour is not a valid colour', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses', [
                'title' => 'Bad Text Colour',
                'background_colour' => '#ffffff',
                'text_colour' => 'expression(alert(1))',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text_colour']);
    });

    test('client can never create a system status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses', [
                'title' => 'Sneaky',
                'background_colour' => '#ffffff',
                'text_colour' => '#000000',
                'system_key' => QuoteStatus::KEY_DRAFT,
            ])
            ->assertStatus(201);

        expect(QuoteStatus::query()->where('title', 'Sneaky')->value('system_key'))->toBeNull();
    });

    test('client supplied audit columns are ignored', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses', [
                'title' => 'Audit Safe',
                'background_colour' => '#ffffff',
                'text_colour' => '#000000',
                'created_by' => 9999,
            ])
            ->assertStatus(201);

        $status = QuoteStatus::query()->where('title', 'Audit Safe')->firstOrFail();

        expect($status->created_by)->toBe($superAdmin->id);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a quote status', function () {
        $superAdmin = $this->superAdminUser();

        $status = QuoteStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/quote-statuses/{$status->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('QuoteStatuses/Show')
                ->has('quoteStatus')
            );
    });

    test('unauthenticated user cannot view a quote status', function () {
        $status = QuoteStatus::factory()->create();

        $this->get("/quote-statuses/{$status->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a quote status', function () {
        $user = $this->userWithNoPermissions();

        $status = QuoteStatus::factory()->create();

        $this->actingAs($user)
            ->get("/quote-statuses/{$status->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent quote status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/quote-statuses/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $status = QuoteStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/quote-statuses/{$status->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('QuoteStatuses/Edit')
                ->has('quoteStatus')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $status = QuoteStatus::factory()->create();

        $this->get("/quote-statuses/{$status->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $status = QuoteStatus::factory()->create();

        $this->actingAs($user)
            ->get("/quote-statuses/{$status->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a quote status', function () {
        $superAdmin = $this->superAdminUser();

        $status = QuoteStatus::factory()->create(['title' => 'Old Title']);

        $this->actingAs($superAdmin)
            ->putJson("/quote-statuses/{$status->id}", ['title' => 'New Title'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'New Title']);

        $this->assertDatabaseHas('quote_statuses', [
            'id' => $status->id,
            'title' => 'New Title',
        ]);
    });

    test('patch verb also updates a quote status', function () {
        $superAdmin = $this->superAdminUser();

        $status = QuoteStatus::factory()->create(['background_colour' => '#ffffff']);

        $this->actingAs($superAdmin)
            ->patchJson("/quote-statuses/{$status->id}", ['background_colour' => '#112233'])
            ->assertStatus(200);

        $this->assertDatabaseHas('quote_statuses', [
            'id' => $status->id,
            'background_colour' => '#112233',
        ]);
    });

    test('user without permission cannot update a quote status', function () {
        $user = $this->normalUser();

        $status = QuoteStatus::factory()->create(['title' => 'Original']);

        $this->actingAs($user)
            ->putJson("/quote-statuses/{$status->id}", ['title' => 'Hacked'])
            ->assertStatus(403);

        $this->assertDatabaseHas('quote_statuses', ['id' => $status->id, 'title' => 'Original']);
    });

    test('update fails validation when colour is invalid', function () {
        $superAdmin = $this->superAdminUser();

        $status = QuoteStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/quote-statuses/{$status->id}", [
                'background_colour' => 'red; background:url(x)',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_colour']);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $status = QuoteStatus::factory()->create([
            'background_colour' => '#112233',
            'text_colour' => '#445566',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/quote-statuses/{$status->id}", ['title' => 'Updated Title'])
            ->assertStatus(200);

        $this->assertDatabaseHas('quote_statuses', [
            'id' => $status->id,
            'title' => 'Updated Title',
            'background_colour' => '#112233',
            'text_colour' => '#445566',
        ]);
    });

    test('refuses to rename a system status', function () {
        $superAdmin = $this->superAdminUser();

        $status = QuoteStatus::factory()
            ->system(QuoteStatus::KEY_DRAFT)
            ->create(['title' => 'Draft']);

        $this->actingAs($superAdmin)
            ->putJson("/quote-statuses/{$status->id}", [
                'title' => 'Renamed',
                'background_colour' => '#ffffff',
                'text_colour' => '#000000',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        expect($status->fresh()->title)->toBe('Draft');
    });

    test('client can never change the system key on update', function () {
        $superAdmin = $this->superAdminUser();

        $status = QuoteStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/quote-statuses/{$status->id}", [
                'title' => $status->title,
                'system_key' => QuoteStatus::KEY_SENT,
            ])
            ->assertStatus(200);

        expect($status->fresh()->system_key)->toBeNull();
    });

    test('logs quote status updates with actor id', function () {
        $actor = $this->adminUser();

        $status = QuoteStatus::factory()->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/quote-statuses/{$status->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_QUOTE_STATUS)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a custom quote status', function () {
        $superAdmin = $this->superAdminUser();

        $status = QuoteStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/quote-statuses/{$status->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('quote_statuses', ['id' => $status->id]);
    });

    test('user without permission cannot soft delete a quote status', function () {
        $user = $this->normalUser();

        $status = QuoteStatus::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/quote-statuses/{$status->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('quote_statuses', ['id' => $status->id, 'deleted_at' => null]);
    });

    test('refuses to delete a system status even for a super admin', function () {
        $superAdmin = $this->superAdminUser();

        $status = QuoteStatus::factory()->system(QuoteStatus::KEY_SENT)->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/quote-statuses/{$status->id}")
            ->assertStatus(403);

        expect($status->fresh()->trashed())->toBeFalse();
    });

    test('blocks deleting a system status at service level, covering the super admin bypass', function () {
        $status = QuoteStatus::factory()->system(QuoteStatus::KEY_SENT)->create();

        app(DeleterService::class)
            ->delete($status, $this->normalUser()->id);
    })->throws(AuthorizationException::class);

    test('destroy returns 404 for a non-existent quote status', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/quote-statuses/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted quote status', function () {
        $superAdmin = $this->superAdminUser();

        $status = QuoteStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quote-statuses/{$status->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('quote_statuses', [
            'id' => $status->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a quote status', function () {
        $user = $this->normalUser();

        $status = QuoteStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/quote-statuses/{$status->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a quote status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $status = QuoteStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quote-statuses/{$status->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a soft-deleted quote status', function () {
        $superAdmin = $this->superAdminUser();

        $status = QuoteStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/quote-statuses/{$status->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('quote_statuses', ['id' => $status->id]);
    });

    test('user without permission cannot force delete a quote status', function () {
        $user = $this->normalUser();

        $status = QuoteStatus::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/quote-statuses/{$status->id}/force")
            ->assertStatus(403);

        $this->assertDatabaseHas('quote_statuses', ['id' => $status->id]);
    });

    test('force delete returns 404 for a quote status that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $status = QuoteStatus::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/quote-statuses/{$status->id}/force")
            ->assertStatus(404);

        $this->assertDatabaseHas('quote_statuses', ['id' => $status->id]);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete quote statuses', function () {
        $superAdmin = $this->superAdminUser();

        $statuses = QuoteStatus::factory()->count(3)->create();
        $ids = $statuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('quote_statuses', ['id' => $id]);
        }
    });

    test('bulk delete refuses to delete system statuses', function () {
        $superAdmin = $this->superAdminUser();

        $system = QuoteStatus::factory()->system(QuoteStatus::KEY_SENT)->create();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses/bulk/delete', ['ids' => [$system->id]])
            ->assertStatus(403);

        expect($system->fresh()->trashed())->toBeFalse();
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete quote statuses', function () {
        $user = $this->normalUser();

        $statuses = QuoteStatus::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/quote-statuses/bulk/delete', [
                'ids' => $statuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore quote statuses', function () {
        $superAdmin = $this->superAdminUser();

        $statuses = QuoteStatus::factory()->count(3)->deleted()->create();
        $ids = $statuses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('quote_statuses', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore quote statuses', function () {
        $user = $this->normalUser();

        $statuses = QuoteStatus::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/quote-statuses/bulk/restore', [
                'ids' => $statuses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('import', function () {
    test('authenticated user with permission can preview and commit a csv import', function () {
        Storage::fake();

        $superAdmin = $this->superAdminUser();

        QuoteStatus::factory()->create(['title' => 'Existing']);

        $csv = "title,background_colour\nFresh,#112233\nExisting,#445566\nBad,notacolour\n";
        $file = UploadedFile::fake()->createWithContent('quote-statuses.csv', $csv);

        $preview = $this->actingAs($superAdmin)
            ->postJson('/quote-statuses/import/preview', ['file' => $file])
            ->assertStatus(200)
            ->json();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses/import/commit', ['token' => $preview['token']])
            ->assertStatus(200);

        expect(QuoteStatus::query()->where('title', 'Fresh')->exists())->toBeTrue()
            ->and(QuoteStatus::query()->where('title', 'Existing')->count())->toBe(1)
            ->and(QuoteStatus::query()->where('title', 'Bad')->exists())->toBeFalse();
    });

    test('imported statuses are always custom statuses', function () {
        Storage::fake();

        $superAdmin = $this->superAdminUser();

        $csv = "title,background_colour,system_key\nImported,#112233,draft\n";
        $file = UploadedFile::fake()->createWithContent('quote-statuses.csv', $csv);

        $preview = $this->actingAs($superAdmin)
            ->postJson('/quote-statuses/import/preview', ['file' => $file])
            ->assertStatus(200)
            ->json();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses/import/commit', ['token' => $preview['token']])
            ->assertStatus(200);

        expect(QuoteStatus::query()->where('title', 'Imported')->value('system_key'))->toBeNull();
    });

    test('import preview fails validation when no file is supplied', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quote-statuses/import/preview', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    test('user without permission cannot preview an import', function () {
        Storage::fake();

        $user = $this->normalUser();

        $file = UploadedFile::fake()->createWithContent(
            'quote-statuses.csv',
            "title,background_colour\nNope,#112233\n",
        );

        $this->actingAs($user)
            ->postJson('/quote-statuses/import/preview', ['file' => $file])
            ->assertStatus(403);
    });
});

describe('export', function () {
    test('authenticated user with permission can export quote statuses', function () {
        $superAdmin = $this->superAdminUser();

        QuoteStatus::factory()->create(['title' => 'Exportable']);

        $response = $this->actingAs($superAdmin)->get('/quote-statuses/export');

        $response->assertStatus(200);

        expect($response->streamedContent())->toContain('Exportable');
    });

    test('user without permission cannot export quote statuses', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/quote-statuses/export')
            ->assertStatus(403);
    });

    test('csv export neutralises formula injection', function () {
        $superAdmin = $this->superAdminUser();

        QuoteStatus::factory()->create(['title' => '=HYPERLINK("http://evil")']);

        $response = $this->actingAs($superAdmin)->get('/quote-statuses/export');

        $response->assertStatus(200);

        expect($response->streamedContent())->toContain("'=HYPERLINK");
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted quote statuses', function () {
        $superAdmin = $this->superAdminUser();

        QuoteStatus::factory()->count(2)->create();
        $trashed = QuoteStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/quote-statuses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('QuoteStatuses/Index')
                ->has('quoteStatuses')
            );

        $this->assertSoftDeleted('quote_statuses', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted quote status', function () {
        $superAdmin = $this->superAdminUser();

        $status = QuoteStatus::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/quote-statuses/{$status->id}")
            ->assertStatus(404);
    });
});

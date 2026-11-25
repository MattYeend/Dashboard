<?php

declare(strict_types=1);

use App\Models\EmailSuppression;
use App\Models\Log;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

if (! function_exists('emailSuppressionPayload')) {
    /**
     * Build a valid email suppression payload.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    function emailSuppressionPayload(array $overrides = []): array
    {
        return array_merge([
            'email' => 'stop@example.org',
            'reason' => 'manual',
        ], $overrides);
    }
}

describe('index', function () {
    test('authenticated user with permission can list email suppressions', function () {
        $superAdmin = $this->superAdminUser();

        EmailSuppression::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/email-suppressions')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailSuppressions/Index', false)
                ->has('emailSuppressions')
                ->has('permissions_meta')
            );
    });

    test('unauthenticated user cannot list email suppressions', function () {
        $this->get('/email-suppressions')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list email suppressions', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/email-suppressions')
            ->assertStatus(403);
    });

    test('index can be searched by email', function () {
        $superAdmin = $this->superAdminUser();

        EmailSuppression::factory()->create(['email' => 'alpha@example.org']);
        EmailSuppression::factory()->create(['email' => 'bravo@example.org']);

        $this->actingAs($superAdmin)
            ->get('/email-suppressions?search=alpha')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailSuppressions/Index', false)
                ->has('emailSuppressions.data', 1)
            );
    });

    test('index can be filtered by reason', function () {
        $superAdmin = $this->superAdminUser();

        EmailSuppression::factory()->bounced()->create();
        EmailSuppression::factory()->complained()->create();
        EmailSuppression::factory()->complained()->create();

        $this->actingAs($superAdmin)
            ->get('/email-suppressions?reason=complained')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailSuppressions/Index', false)
                ->has('emailSuppressions.data', 2)
            );
    });

    test('index ignores an unknown reason filter', function () {
        $superAdmin = $this->superAdminUser();

        EmailSuppression::factory()->count(2)->create();

        $this->actingAs($superAdmin)
            ->get('/email-suppressions?reason=not-a-reason')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailSuppressions/Index', false)
                ->has('emailSuppressions.data', 2)
            );
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/email-suppressions/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailSuppressions/Create', false)
                ->has('reasonOptions', 4)
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/email-suppressions/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/email-suppressions/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create an email suppression', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions', emailSuppressionPayload())
            ->assertStatus(201)
            ->assertJsonFragment(['email' => 'stop@example.org']);

        $this->assertDatabaseHas('email_suppressions', [
            'email' => 'stop@example.org',
            'reason' => 'manual',
        ]);
    });

    test('store lower-cases and trims the email', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions', emailSuppressionPayload(['email' => '  Stop@Example.ORG  ']))
            ->assertStatus(201);

        $this->assertDatabaseHas('email_suppressions', ['email' => 'stop@example.org']);
    });

    test('store records when the address was suppressed', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions', emailSuppressionPayload())
            ->assertStatus(201);

        expect(EmailSuppression::where('email', 'stop@example.org')->value('suppressed_at'))
            ->not->toBeNull();
    });

    test('store redirects to the show page for non-JSON requests', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->post('/email-suppressions', emailSuppressionPayload(['email' => 'redirect@example.org']))
            ->assertRedirect();

        $emailSuppression = EmailSuppression::where('email', 'redirect@example.org')->firstOrFail();

        expect($emailSuppression->id)->toBeInt();
    });

    test('user without permission cannot create an email suppression', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/email-suppressions', emailSuppressionPayload())
            ->assertStatus(403);
    });

    test('unauthenticated user cannot create an email suppression', function () {
        $this->postJson('/email-suppressions', emailSuppressionPayload())
            ->assertStatus(401);
    });

    test('store fails validation with an empty payload', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'reason']);
    });

    test('store fails validation when email is not valid', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions', emailSuppressionPayload(['email' => 'not-an-email']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('store fails validation when email exceeds max length', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions', emailSuppressionPayload([
                'email' => str_repeat('a', 250).'@example.org',
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('store fails validation when reason is not in the allow-list', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions', emailSuppressionPayload(['reason' => 'because']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    });

    test('store fails validation when notes exceed max length', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions', emailSuppressionPayload(['notes' => str_repeat('a', 2001)]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['notes']);
    });

    test('store rejects a duplicate email regardless of case', function () {
        $superAdmin = $this->superAdminUser();

        EmailSuppression::factory()->create(['email' => 'stop@example.org']);

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions', emailSuppressionPayload(['email' => 'STOP@example.org']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('store rejects an email that exists as a soft-deleted row', function () {
        $superAdmin = $this->superAdminUser();

        EmailSuppression::factory()->deleted()->create(['email' => 'stop@example.org']);

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions', emailSuppressionPayload())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('store ignores system managed fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions', emailSuppressionPayload([
                'campaign_id' => 999,
                'contact_id' => 999,
                'created_by' => 999,
            ]))
            ->assertStatus(201);

        $this->assertDatabaseHas('email_suppressions', [
            'email' => 'stop@example.org',
            'campaign_id' => null,
            'contact_id' => null,
            'created_by' => $superAdmin->id,
        ]);
    });

    test('logs email suppression creation with actor id', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions', emailSuppressionPayload())
            ->assertStatus(201);

        $log = Log::query()
            ->where('action_id', Log::ACTION_CREATE_EMAIL_SUPPRESSION)
            ->where('logged_in_user_id', $superAdmin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKey('after');
    });
});

describe('show', function () {
    test('authenticated user with permission can view an email suppression', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/email-suppressions/{$emailSuppression->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailSuppressions/Show', false)
                ->has('emailSuppression')
            );
    });

    test('unauthenticated user cannot view an email suppression', function () {
        $emailSuppression = EmailSuppression::factory()->create();

        $this->get("/email-suppressions/{$emailSuppression->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view an email suppression', function () {
        $user = $this->userWithNoPermissions();

        $emailSuppression = EmailSuppression::factory()->create();

        $this->actingAs($user)
            ->get("/email-suppressions/{$emailSuppression->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent email suppression', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/email-suppressions/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/email-suppressions/{$emailSuppression->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailSuppressions/Edit', false)
                ->has('emailSuppression')
                ->has('reasonOptions')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $emailSuppression = EmailSuppression::factory()->create();

        $this->get("/email-suppressions/{$emailSuppression->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $emailSuppression = EmailSuppression::factory()->create();

        $this->actingAs($user)
            ->get("/email-suppressions/{$emailSuppression->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update an email suppression', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->create(['reason' => 'manual']);

        $this->actingAs($superAdmin)
            ->putJson("/email-suppressions/{$emailSuppression->id}", ['reason' => 'bounced'])
            ->assertStatus(200)
            ->assertJsonFragment(['reason' => 'bounced']);

        $this->assertDatabaseHas('email_suppressions', [
            'id' => $emailSuppression->id,
            'reason' => 'bounced',
            'updated_by' => $superAdmin->id,
        ]);
    });

    test('patch verb also updates an email suppression', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->create(['notes' => 'Old']);

        $this->actingAs($superAdmin)
            ->patchJson("/email-suppressions/{$emailSuppression->id}", ['notes' => 'Patched'])
            ->assertStatus(200)
            ->assertJsonFragment(['notes' => 'Patched']);
    });

    test('user without permission cannot update an email suppression', function () {
        $user = $this->normalUser();

        $emailSuppression = EmailSuppression::factory()->create();

        $this->actingAs($user)
            ->putJson("/email-suppressions/{$emailSuppression->id}", ['reason' => 'bounced'])
            ->assertStatus(403);
    });

    test('update fails validation when email is set to null', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->create();

        $this->actingAs($superAdmin)
            ->patchJson("/email-suppressions/{$emailSuppression->id}", ['email' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('update fails validation when reason is not in the allow-list', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->create();

        $this->actingAs($superAdmin)
            ->patchJson("/email-suppressions/{$emailSuppression->id}", ['reason' => 'because'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    });

    test('update rejects an email that belongs to another entry', function () {
        $superAdmin = $this->superAdminUser();

        EmailSuppression::factory()->create(['email' => 'taken@example.org']);
        $emailSuppression = EmailSuppression::factory()->create();

        $this->actingAs($superAdmin)
            ->patchJson("/email-suppressions/{$emailSuppression->id}", ['email' => 'taken@example.org'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('update allows an entry to keep its own email', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->create(['email' => 'same@example.org']);

        $this->actingAs($superAdmin)
            ->patchJson("/email-suppressions/{$emailSuppression->id}", [
                'email' => 'Same@Example.org',
                'reason' => 'complained',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('email_suppressions', [
            'id' => $emailSuppression->id,
            'email' => 'same@example.org',
            'reason' => 'complained',
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->create([
            'email' => 'keep@example.org',
            'reason' => 'bounced',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/email-suppressions/{$emailSuppression->id}", ['notes' => 'Updated notes'])
            ->assertStatus(200);

        $this->assertDatabaseHas('email_suppressions', [
            'id' => $emailSuppression->id,
            'email' => 'keep@example.org',
            'reason' => 'bounced',
            'notes' => 'Updated notes',
        ]);
    });

    test('update redirects to the show page for non-JSON requests', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->create();

        $this->actingAs($superAdmin)
            ->put("/email-suppressions/{$emailSuppression->id}", ['notes' => 'Redirected'])
            ->assertRedirect("/email-suppressions/{$emailSuppression->id}");
    });

    test('logs email suppression updates with actor id', function () {
        $actor = $this->adminUser();

        $emailSuppression = EmailSuppression::factory()->create(['reason' => 'manual']);

        $this->actingAs($actor)
            ->putJson("/email-suppressions/{$emailSuppression->id}", ['reason' => 'bounced'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_EMAIL_SUPPRESSION)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('admin can soft delete an email suppression', function () {
        $admin = $this->adminUser();

        $emailSuppression = EmailSuppression::factory()->create();

        $this->actingAs($admin)
            ->deleteJson("/email-suppressions/{$emailSuppression->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('email_suppressions', ['id' => $emailSuppression->id]);
    });

    test('user without permission cannot soft delete an email suppression', function () {
        $user = $this->normalUser();

        $emailSuppression = EmailSuppression::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/email-suppressions/{$emailSuppression->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent email suppression', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/email-suppressions/99999')
            ->assertStatus(404);
    });

    test('destroy records the deleting user', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/email-suppressions/{$emailSuppression->id}")
            ->assertStatus(204);

        $this->assertDatabaseHas('email_suppressions', [
            'id' => $emailSuppression->id,
            'deleted_by' => $superAdmin->id,
        ]);
    });

    test('logs email suppression deletion with actor id', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/email-suppressions/{$emailSuppression->id}")
            ->assertStatus(204);

        $log = Log::query()
            ->where('action_id', Log::ACTION_DELETE_EMAIL_SUPPRESSION)
            ->where('logged_in_user_id', $superAdmin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKey('before');
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted email suppression', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/email-suppressions/{$emailSuppression->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('email_suppressions', [
            'id' => $emailSuppression->id,
            'deleted_at' => null,
            'restored_by' => $superAdmin->id,
        ]);
    });

    test('user without permission cannot restore an email suppression', function () {
        $user = $this->normalUser();

        $emailSuppression = EmailSuppression::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/email-suppressions/{$emailSuppression->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for an email suppression that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/email-suppressions/{$emailSuppression->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete an email suppression', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/email-suppressions/{$emailSuppression->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('email_suppressions', ['id' => $emailSuppression->id]);
    });

    test('user without permission cannot force delete an email suppression', function () {
        $user = $this->normalUser();

        $emailSuppression = EmailSuppression::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/email-suppressions/{$emailSuppression->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for an email suppression that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/email-suppressions/{$emailSuppression->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete email suppressions', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppressions = EmailSuppression::factory()->count(3)->create();
        $ids = $emailSuppressions->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('email_suppressions', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete email suppressions', function () {
        $user = $this->normalUser();

        $emailSuppressions = EmailSuppression::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/email-suppressions/bulk/delete', [
                'ids' => $emailSuppressions->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore email suppressions', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppressions = EmailSuppression::factory()->count(3)->deleted()->create();
        $ids = $emailSuppressions->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('email_suppressions', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore email suppressions', function () {
        $user = $this->normalUser();

        $emailSuppressions = EmailSuppression::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/email-suppressions/bulk/restore', [
                'ids' => $emailSuppressions->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('export', function () {
    test('authenticated user with permission can export email suppressions as csv', function () {
        $superAdmin = $this->superAdminUser();

        EmailSuppression::factory()->count(2)->create();

        $response = $this->actingAs($superAdmin)
            ->get('/email-suppressions/export')
            ->assertStatus(200);

        expect($response->headers->get('content-type'))->toContain('text/csv');
    });

    test('export contains the email address', function () {
        $superAdmin = $this->superAdminUser();

        EmailSuppression::factory()->create(['email' => 'exported@example.org']);

        $response = $this->actingAs($superAdmin)
            ->get('/email-suppressions/export')
            ->assertStatus(200);

        expect($response->streamedContent())->toContain('exported@example.org');
    });

    test('export neutralises spreadsheet formula injection', function () {
        $superAdmin = $this->superAdminUser();

        EmailSuppression::factory()->create(['email' => '=cmd|calc@example.org']);

        $response = $this->actingAs($superAdmin)
            ->get('/email-suppressions/export')
            ->assertStatus(200);

        expect($response->streamedContent())->not->toContain(',=cmd')
            ->and($response->streamedContent())->not->toMatch('/^=cmd/m');
    });

    test('export logs the action', function () {
        $superAdmin = $this->superAdminUser();

        EmailSuppression::factory()->create();

        $this->actingAs($superAdmin)
            ->get('/email-suppressions/export')
            ->assertStatus(200);

        expect(
            Log::query()->where('action_id', Log::ACTION_EXPORT_EMAIL_SUPPRESSION)->exists()
        )->toBeTrue();
    });

    test('unauthenticated user cannot export email suppressions', function () {
        $this->get('/email-suppressions/export')
            ->assertRedirect('/login');
    });

    test('user without permission cannot export email suppressions', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/email-suppressions/export')
            ->assertStatus(403);
    });
});

describe('import', function () {
    test('user without permission cannot preview an import', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/email-suppressions/import/preview', [
                'file' => UploadedFile::fake()->createWithContent(
                    'email-suppressions.csv',
                    "email,reason\nstop@example.org,manual\n"
                ),
            ])
            ->assertStatus(403);
    });

    test('preview fails validation when file is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions/import/preview', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    test('preview fails validation when file is not a csv', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions/import/preview', [
                'file' => UploadedFile::fake()->create('email-suppressions.pdf', 100, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    test('preview returns a token without persisting any email suppressions', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions/import/preview', [
                'file' => UploadedFile::fake()->createWithContent(
                    'email-suppressions.csv',
                    "email,reason\nimported@example.org,manual\n"
                ),
            ])
            ->assertStatus(200)
            ->assertJsonStructure(['token']);

        $this->assertDatabaseMissing('email_suppressions', ['email' => 'imported@example.org']);
    });

    test('a previewed import can be committed', function () {
        $superAdmin = $this->superAdminUser();

        $token = $this->actingAs($superAdmin)
            ->postJson('/email-suppressions/import/preview', [
                'file' => UploadedFile::fake()->createWithContent(
                    'email-suppressions.csv',
                    "email,reason\nImported@Example.org,bounced\n"
                ),
            ])
            ->assertStatus(200)
            ->json('token');

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions/import/commit', ['token' => $token])
            ->assertStatus(200);

        $this->assertDatabaseHas('email_suppressions', [
            'email' => 'imported@example.org',
            'reason' => 'bounced',
            'created_by' => $superAdmin->id,
        ]);
    });

    test('one-shot import skips rows that are already suppressed', function () {
        $superAdmin = $this->superAdminUser();

        EmailSuppression::factory()->create(['email' => 'existing@example.org']);
        EmailSuppression::factory()->deleted()->create(['email' => 'trashed@example.org']);

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions/import', [
                'file' => UploadedFile::fake()->createWithContent(
                    'email-suppressions.csv',
                    "email,reason\nexisting@example.org,manual\ntrashed@example.org,manual\nnew@example.org,manual\n"
                ),
            ])
            ->assertStatus(200)
            ->assertJsonPath('imported', 1)
            ->assertJsonCount(2, 'skipped');

        $this->assertDatabaseHas('email_suppressions', ['email' => 'new@example.org']);
    });

    test('one-shot import rejects invalid emails and reasons', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions/import', [
                'file' => UploadedFile::fake()->createWithContent(
                    'email-suppressions.csv',
                    "email,reason\nnot-an-email,manual\nvalid@example.org,because\n"
                ),
            ])
            ->assertStatus(200)
            ->assertJsonPath('imported', 0)
            ->assertJsonCount(2, 'skipped');
    });

    test('commit fails validation when token is not a uuid', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions/import/commit', [
                'token' => 'not-a-uuid',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    });

    test('commit fails validation when token is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-suppressions/import/commit', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    });

    test('user without permission cannot commit an import', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/email-suppressions/import/commit', [
                'token' => Str::uuid()->toString(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted email suppressions', function () {
        $superAdmin = $this->superAdminUser();

        EmailSuppression::factory()->count(2)->create();
        $trashed = EmailSuppression::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/email-suppressions')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailSuppressions/Index', false)
                ->has('emailSuppressions.data', 2)
            );

        $this->assertSoftDeleted('email_suppressions', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted email suppression', function () {
        $superAdmin = $this->superAdminUser();

        $emailSuppression = EmailSuppression::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/email-suppressions/{$emailSuppression->id}")
            ->assertStatus(404);
    });

    test('index can include trashed email suppressions when requested', function () {
        $superAdmin = $this->superAdminUser();

        EmailSuppression::factory()->count(2)->create();
        EmailSuppression::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/email-suppressions?trashed=with')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailSuppressions/Index', false)
                ->has('emailSuppressions.data', 3)
            );
    });
});
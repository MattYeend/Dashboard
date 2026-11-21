<?php

declare(strict_types=1);

use App\Models\EmailTemplate;
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

describe('index', function () {
    test('authenticated user with permission can list email templates', function () {
        $superAdmin = $this->superAdminUser();

        EmailTemplate::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/email-templates')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailTemplates/Index', false)
                ->has('emailTemplates')
                ->has('permissions_meta')
            );
    });

    test('unauthenticated user cannot list email templates', function () {
        $this->get('/email-templates')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list email templates', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/email-templates')
            ->assertStatus(403);
    });

    test('index can be searched by name', function () {
        $superAdmin = $this->superAdminUser();

        EmailTemplate::factory()->create(['name' => 'Spring Offer']);
        EmailTemplate::factory()->create(['name' => 'Autumn Newsletter']);

        $this->actingAs($superAdmin)
            ->get('/email-templates?search=Spring')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailTemplates/Index', false)
                ->has('emailTemplates.data', 1)
            );
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/email-templates/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailTemplates/Create', false)
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/email-templates/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/email-templates/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create an email template', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'name' => 'Spring Offer',
            'subject' => 'Spring offer for you',
            'body_html' => '<p>Hello {{ first_name }}</p>',
            'is_active' => true,
        ];

        $this->actingAs($superAdmin)
            ->postJson('/email-templates', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Spring Offer']);

        $this->assertDatabaseHas('email_templates', [
            'name' => 'Spring Offer',
            'subject' => 'Spring offer for you',
            'is_active' => true,
        ]);
    });

    test('store redirects to the show page for non-JSON requests', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->post('/email-templates', [
                'name' => 'Redirect Template',
                'subject' => 'Redirect subject',
                'body_html' => '<p>Redirect</p>',
                'is_active' => true,
            ])
            ->assertRedirect();

        $emailTemplate = EmailTemplate::where('name', 'Redirect Template')->firstOrFail();

        expect($emailTemplate->id)->toBeInt();
    });

    test('user without permission cannot create an email template', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/email-templates', [
                'name' => 'Blocked Template',
                'subject' => 'Blocked subject',
                'body_html' => '<p>Blocked</p>',
                'is_active' => true,
            ])
            ->assertStatus(403);
    });

    test('unauthenticated user cannot create an email template', function () {
        $this->postJson('/email-templates', [
            'name' => 'Anonymous Template',
            'subject' => 'Anonymous subject',
            'body_html' => '<p>Anonymous</p>',
            'is_active' => true,
        ])->assertStatus(401);
    });

    test('store fails validation with an empty payload', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'subject', 'body_html']);
    });

    test('store fails validation when name is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates', [
                'subject' => 'Spring offer for you',
                'body_html' => '<p>Hello</p>',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('store fails validation when name exceeds max length', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates', [
                'name' => str_repeat('a', 256),
                'subject' => 'Spring offer for you',
                'body_html' => '<p>Hello</p>',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('store fails validation when subject is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates', [
                'name' => 'Spring Offer',
                'body_html' => '<p>Hello</p>',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['subject']);
    });

    test('store fails validation when subject exceeds max length', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates', [
                'name' => 'Spring Offer',
                'subject' => str_repeat('a', 256),
                'body_html' => '<p>Hello</p>',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['subject']);
    });

    test('store fails validation when body_html is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates', [
                'name' => 'Spring Offer',
                'subject' => 'Spring offer for you',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['body_html']);
    });

    test('store fails validation when is_active is not a boolean', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates', [
                'name' => 'Spring Offer',
                'subject' => 'Spring offer for you',
                'body_html' => '<p>Hello</p>',
                'is_active' => 'not-a-boolean',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_active']);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates', [
                'name' => 'Template with meta',
                'subject' => 'Meta subject',
                'body_html' => '<p>Hello</p>',
                'is_active' => true,
                'meta' => ['category' => 'marketing'],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('email_templates', ['name' => 'Template with meta']);
    });

    test('logs email template creation with actor id', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates', [
                'name' => 'Logged Template',
                'subject' => 'Logged subject',
                'body_html' => '<p>Logged</p>',
                'is_active' => true,
            ])
            ->assertStatus(201);

        $log = Log::query()
            ->where('action_id', Log::ACTION_CREATE_EMAIL_TEMPLATE)
            ->where('logged_in_user_id', $superAdmin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKey('after');
    });
});

describe('show', function () {
    test('authenticated user with permission can view an email template', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/email-templates/{$emailTemplate->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailTemplates/Show', false)
                ->has('emailTemplate')
            );
    });

    test('unauthenticated user cannot view an email template', function () {
        $emailTemplate = EmailTemplate::factory()->create();

        $this->get("/email-templates/{$emailTemplate->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view an email template', function () {
        $user = $this->userWithNoPermissions();

        $emailTemplate = EmailTemplate::factory()->create();

        $this->actingAs($user)
            ->get("/email-templates/{$emailTemplate->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent email template', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/email-templates/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/email-templates/{$emailTemplate->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailTemplates/Edit', false)
                ->has('emailTemplate')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $emailTemplate = EmailTemplate::factory()->create();

        $this->get("/email-templates/{$emailTemplate->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $emailTemplate = EmailTemplate::factory()->create();

        $this->actingAs($user)
            ->get("/email-templates/{$emailTemplate->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update an email template', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->create(['name' => 'Old Name']);

        $this->actingAs($superAdmin)
            ->putJson("/email-templates/{$emailTemplate->id}", ['name' => 'New Name'])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);

        $this->assertDatabaseHas('email_templates', [
            'id' => $emailTemplate->id,
            'name' => 'New Name',
        ]);
    });

    test('patch verb also updates an email template', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->create(['subject' => 'Old subject']);

        $this->actingAs($superAdmin)
            ->patchJson("/email-templates/{$emailTemplate->id}", ['subject' => 'New subject'])
            ->assertStatus(200)
            ->assertJsonFragment(['subject' => 'New subject']);
    });

    test('user without permission cannot update an email template', function () {
        $user = $this->normalUser();

        $emailTemplate = EmailTemplate::factory()->create();

        $this->actingAs($user)
            ->putJson("/email-templates/{$emailTemplate->id}", ['name' => 'Blocked'])
            ->assertStatus(403);
    });

    test('update fails validation when name is set to null', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->create();

        $this->actingAs($superAdmin)
            ->patchJson("/email-templates/{$emailTemplate->id}", ['name' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('update fails validation when subject is set to null', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->create();

        $this->actingAs($superAdmin)
            ->patchJson("/email-templates/{$emailTemplate->id}", ['subject' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['subject']);
    });

    test('update fails validation when body_html is set to null', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->create();

        $this->actingAs($superAdmin)
            ->patchJson("/email-templates/{$emailTemplate->id}", ['body_html' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['body_html']);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->create([
            'name' => 'Original name',
            'subject' => 'Original subject',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/email-templates/{$emailTemplate->id}", ['name' => 'Updated name'])
            ->assertStatus(200);

        $this->assertDatabaseHas('email_templates', [
            'id' => $emailTemplate->id,
            'name' => 'Updated name',
            'subject' => 'Original subject',
        ]);
    });

    test('update redirects to the show page for non-JSON requests', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->create();

        $this->actingAs($superAdmin)
            ->put("/email-templates/{$emailTemplate->id}", [
                'name' => 'Redirected Update',
            ])
            ->assertRedirect("/email-templates/{$emailTemplate->id}");
    });

    test('logs email template updates with actor id', function () {
        $actor = $this->adminUser();

        $emailTemplate = EmailTemplate::factory()->create(['name' => 'Old Name']);

        $this->actingAs($actor)
            ->putJson("/email-templates/{$emailTemplate->id}", ['name' => 'New Name'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_EMAIL_TEMPLATE)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete an email template', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/email-templates/{$emailTemplate->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('email_templates', ['id' => $emailTemplate->id]);
    });

    test('user without permission cannot soft delete an email template', function () {
        $user = $this->normalUser();

        $emailTemplate = EmailTemplate::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/email-templates/{$emailTemplate->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent email template', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/email-templates/99999')
            ->assertStatus(404);
    });

    test('destroy records the deleting user', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/email-templates/{$emailTemplate->id}")
            ->assertStatus(204);

        $this->assertDatabaseHas('email_templates', [
            'id' => $emailTemplate->id,
            'deleted_by' => $superAdmin->id,
        ]);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted email template', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/email-templates/{$emailTemplate->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('email_templates', [
            'id' => $emailTemplate->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore an email template', function () {
        $user = $this->normalUser();

        $emailTemplate = EmailTemplate::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/email-templates/{$emailTemplate->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for an email template that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/email-templates/{$emailTemplate->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete an email template', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/email-templates/{$emailTemplate->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('email_templates', ['id' => $emailTemplate->id]);
    });

    test('user without permission cannot force delete an email template', function () {
        $user = $this->normalUser();

        $emailTemplate = EmailTemplate::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/email-templates/{$emailTemplate->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for an email template that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/email-templates/{$emailTemplate->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete email templates', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplates = EmailTemplate::factory()->count(3)->create();
        $ids = $emailTemplates->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('email_templates', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete email templates', function () {
        $user = $this->normalUser();

        $emailTemplates = EmailTemplate::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/email-templates/bulk/delete', [
                'ids' => $emailTemplates->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore email templates', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplates = EmailTemplate::factory()->count(3)->deleted()->create();
        $ids = $emailTemplates->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('email_templates', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore email templates', function () {
        $user = $this->normalUser();

        $emailTemplates = EmailTemplate::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/email-templates/bulk/restore', [
                'ids' => $emailTemplates->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('export', function () {
    test('authenticated user with permission can export email templates as csv', function () {
        $superAdmin = $this->superAdminUser();

        EmailTemplate::factory()->count(2)->create();

        $response = $this->actingAs($superAdmin)
            ->get('/email-templates/export')
            ->assertStatus(200);

        expect($response->headers->get('content-type'))->toContain('text/csv');
    });

    test('export contains the email template name', function () {
        $superAdmin = $this->superAdminUser();

        EmailTemplate::factory()->create(['name' => 'Exported Template Name']);

        $response = $this->actingAs($superAdmin)
            ->get('/email-templates/export')
            ->assertStatus(200);

        expect($response->streamedContent())->toContain('Exported Template Name');
    });

    test('export neutralises spreadsheet formula injection', function () {
        $superAdmin = $this->superAdminUser();

        EmailTemplate::factory()->create(['name' => '=HYPERLINK("http://example.com")']);

        $response = $this->actingAs($superAdmin)
            ->get('/email-templates/export')
            ->assertStatus(200);

        expect($response->streamedContent())->not->toContain(',=HYPERLINK')
            ->and($response->streamedContent())->not->toMatch('/^=HYPERLINK/m');
    });

    test('unauthenticated user cannot export email templates', function () {
        $this->get('/email-templates/export')
            ->assertRedirect('/login');
    });

    test('user without permission cannot export email templates', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/email-templates/export')
            ->assertStatus(403);
    });
});

describe('import', function () {
    test('user without permission cannot preview an import', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/email-templates/import/preview', [
                'file' => UploadedFile::fake()->createWithContent(
                    'email-templates.csv',
                    "name,subject,body_html\nTest,Test subject,<p>Test</p>\n"
                ),
            ])
            ->assertStatus(403);
    });

    test('preview fails validation when file is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates/import/preview', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    test('preview fails validation when file is not a csv', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates/import/preview', [
                'file' => UploadedFile::fake()->create('email-templates.pdf', 100, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    test('preview returns a token without persisting any email templates', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates/import/preview', [
                'file' => UploadedFile::fake()->createWithContent(
                    'email-templates.csv',
                    "name,subject,body_html\nImported Template,Imported subject,<p>Imported</p>\n"
                ),
            ])
            ->assertStatus(200)
            ->assertJsonStructure(['token']);

        $this->assertDatabaseMissing('email_templates', ['name' => 'Imported Template']);
    });

    test('commit fails validation when token is not a uuid', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates/import/commit', [
                'token' => 'not-a-uuid',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    });

    test('commit fails validation when token is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/email-templates/import/commit', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    });

    test('user without permission cannot commit an import', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/email-templates/import/commit', [
                'token' => Str::uuid()->toString(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted email templates', function () {
        $superAdmin = $this->superAdminUser();

        EmailTemplate::factory()->count(2)->create();
        $trashed = EmailTemplate::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/email-templates')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailTemplates/Index', false)
                ->has('emailTemplates.data', 2)
            );

        $this->assertSoftDeleted('email_templates', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted email template', function () {
        $superAdmin = $this->superAdminUser();

        $emailTemplate = EmailTemplate::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/email-templates/{$emailTemplate->id}")
            ->assertStatus(404);
    });

    test('index can include trashed email templates when requested', function () {
        $superAdmin = $this->superAdminUser();

        EmailTemplate::factory()->count(2)->create();
        EmailTemplate::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/email-templates?trashed=with')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EmailTemplates/Index', false)
                ->has('emailTemplates.data', 3)
            );
    });
});

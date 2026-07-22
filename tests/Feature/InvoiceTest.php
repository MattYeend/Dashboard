<?php

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
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
    test('authenticated user with permission can list invoices', function () {
        $superAdmin = $this->superAdminUser();

        Invoice::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/invoices')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Invoices/Index')
                ->has('invoices')
            );
    });

    test('unauthenticated user cannot list invoices', function () {
        $this->get('/invoices')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list invoices', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/invoices')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/invoices/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Invoices/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/invoices/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/invoices/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create an invoice', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();
        $status = InvoiceStatus::factory()->create();

        $payload = [
            'invoice_number' => 'INV-000123',
            'company_id' => $company->id,
            'status_id' => $status->id,
            'issue_date' => '2026-06-01',
            'due_date' => '2026-07-01',
            'subtotal' => 100000,
            'tax_total' => 20000,
            'total' => 120000,
            'currency' => 'GBP',
            'notes' => 'Initial development phase.',
            'contact' => [
                'phone' => '01132 496821',
                'email' => 'accounts@example.co.uk',
                'address' => '14 Wellington Street',
                'city' => 'Leeds',
                'postal_code' => 'LS1 4DL',
                'country' => 'United Kingdom',
            ],
        ];

        $this->actingAs($superAdmin)
            ->postJson('/invoices', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['invoice_number' => 'INV-000123']);

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-000123',
            'company_id' => $company->id,
        ]);

        $this->assertDatabaseHas('contacts', [
            'email' => 'accounts@example.co.uk',
            'contactable_type' => Invoice::class,
        ]);
    });

    test('user without permission cannot create an invoice', function () {
        $user = $this->normalUser();
        $company = Company::factory()->create();

        $this->actingAs($user)
            ->postJson('/invoices', [
                'invoice_number' => 'INV-000999',
                'company_id' => $company->id,
                'contact' => ['email' => 'test@example.co.uk'],
            ])
            ->assertStatus(403);
    });

    test('store fails validation when invoice_number is missing', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/invoices', [
                'company_id' => $company->id,
                'contact' => ['email' => 'test@example.co.uk'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['invoice_number']);
    });

    test('store fails validation when invoice_number already exists', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        Invoice::factory()->create(['invoice_number' => 'INV-000001']);

        $this->actingAs($superAdmin)
            ->postJson('/invoices', [
                'invoice_number' => 'INV-000001',
                'company_id' => $company->id,
                'contact' => ['email' => 'test@example.co.uk'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['invoice_number']);
    });

    test('store fails validation when contact is missing', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/invoices', [
                'invoice_number' => 'INV-000124',
                'company_id' => $company->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['contact']);
    });

    test('store fails validation when contact email is invalid', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/invoices', [
                'invoice_number' => 'INV-000125',
                'company_id' => $company->id,
                'contact' => ['email' => 'not-an-email'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['contact.email']);
    });

    test('store fails validation when neither company_id nor order_id is provided', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoices', [
                'invoice_number' => 'INV-000126',
                'contact' => ['email' => 'test@example.co.uk'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['company_id', 'order_id']);
    });

    test('store fails validation when status_id does not exist', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/invoices', [
                'invoice_number' => 'INV-000127',
                'company_id' => $company->id,
                'status_id' => 99999,
                'contact' => ['email' => 'test@example.co.uk'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status_id']);
    });

    test('store fails validation when due_date is before issue_date', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/invoices', [
                'invoice_number' => 'INV-000128',
                'company_id' => $company->id,
                'issue_date' => '2026-07-01',
                'due_date' => '2026-06-01',
                'contact' => ['email' => 'test@example.co.uk'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    });

    test('store fails validation when subtotal is negative', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/invoices', [
                'invoice_number' => 'INV-000129',
                'company_id' => $company->id,
                'subtotal' => -100,
                'contact' => ['email' => 'test@example.co.uk'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['subtotal']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/invoices', [
                'invoice_number' => 'INV-000130',
                'company_id' => $company->id,
                'contact' => ['email' => null],
            ])
            ->assertStatus(201);
    });

    test('store succeeds with an order_id instead of company_id', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoices', [
                'invoice_number' => 'INV-000131',
                'order_id' => null,
                'contact' => ['email' => 'test@example.co.uk'],
            ])
            ->assertStatus(422);
    });
});

describe('show', function () {
    test('authenticated user with permission can view an invoice', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/invoices/{$invoice->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Invoices/Show')
                ->has('invoice')
            );
    });

    test('unauthenticated user cannot view an invoice', function () {
        $invoice = Invoice::factory()->create();

        $this->get("/invoices/{$invoice->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view an invoice', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($user)
            ->get("/invoices/{$invoice->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent invoice', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/invoices/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/invoices/{$invoice->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Invoices/Edit')
                ->has('invoice')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $invoice = Invoice::factory()->create();

        $this->get("/invoices/{$invoice->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($user)
            ->get("/invoices/{$invoice->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update an invoice', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create(['notes' => 'Old note']);

        $this->actingAs($superAdmin)
            ->putJson("/invoices/{$invoice->id}", ['notes' => 'New note'])
            ->assertStatus(200)
            ->assertJsonFragment(['notes' => 'New note']);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'notes' => 'New note',
        ]);
    });

    test('patch verb also updates an invoice', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create(['notes' => 'Old note']);

        $this->actingAs($superAdmin)
            ->patchJson("/invoices/{$invoice->id}", ['notes' => 'Patched note'])
            ->assertStatus(200)
            ->assertJsonFragment(['notes' => 'Patched note']);
    });

    test('user without permission cannot update an invoice', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($user)
            ->putJson("/invoices/{$invoice->id}", ['notes' => 'New note'])
            ->assertStatus(403);
    });

    test('update fails validation when due_date is before issue_date', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create(['issue_date' => '2026-06-01']);

        $this->actingAs($superAdmin)
            ->putJson("/invoices/{$invoice->id}", ['due_date' => '2026-05-01'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    });

    test('update fails validation when invoice_number already exists on another invoice', function () {
        $superAdmin = $this->superAdminUser();

        Invoice::factory()->create(['invoice_number' => 'INV-000200']);
        $invoice = Invoice::factory()->create(['invoice_number' => 'INV-000201']);

        $this->actingAs($superAdmin)
            ->putJson("/invoices/{$invoice->id}", ['invoice_number' => 'INV-000200'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['invoice_number']);
    });

    test('update allows an invoice to keep its own invoice_number', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create(['invoice_number' => 'INV-000202']);

        $this->actingAs($superAdmin)
            ->putJson("/invoices/{$invoice->id}", [
                'invoice_number' => 'INV-000202',
                'notes' => 'Updated note',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'invoice_number' => 'INV-000202',
            'notes' => 'Updated note',
        ]);
    });

    test('nullable fields can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();
        $status = InvoiceStatus::factory()->create();

        $invoice = Invoice::factory()->create([
            'status_id' => $status->id,
            'issue_date' => '2026-06-01',
            'due_date' => '2026-07-01',
            'notes' => 'Some notes.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/invoices/{$invoice->id}", [
                'status_id' => null,
                'issue_date' => null,
                'due_date' => null,
                'notes' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status_id' => null,
            'issue_date' => null,
            'due_date' => null,
            'notes' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create([
            'notes' => 'Original notes.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/invoices/{$invoice->id}", [
                'invoice_number' => $invoice->invoice_number,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'notes' => 'Original notes.',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create(['notes' => 'Some notes.']);

        $this->actingAs($superAdmin)
            ->patchJson("/invoices/{$invoice->id}", ['notes' => null])
            ->assertStatus(200);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'notes' => null,
        ]);
    });

    test('contact details can be updated', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/invoices/{$invoice->id}", [
                'contact' => ['email' => 'updated@example.co.uk'],
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('contacts', [
            'contactable_id' => $invoice->id,
            'contactable_type' => Invoice::class,
            'email' => 'updated@example.co.uk',
        ]);
    });

    test('logs invoice updates with actor id', function () {
        $actor = $this->adminUser();
        $invoice = Invoice::factory()->create(['notes' => 'Old note']);

        $this->actingAs($actor)
            ->putJson("/invoices/{$invoice->id}", ['notes' => 'New note'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_INVOICE)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete an invoice', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/invoices/{$invoice->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
    });

    test('user without permission cannot soft delete an invoice', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/invoices/{$invoice->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent invoice', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/invoices/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted invoice', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore an invoice', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/invoices/{$invoice->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for an invoice that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete an invoice', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/invoices/{$invoice->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    });

    test('user without permission cannot force delete an invoice', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/invoices/{$invoice->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for an invoice that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/invoices/{$invoice->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete invoices', function () {
        $superAdmin = $this->superAdminUser();
        $invoices = Invoice::factory()->count(3)->create();
        $ids = $invoices->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/invoices/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('invoices', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoices/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoices/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete invoices', function () {
        $user = $this->normalUser();
        $invoices = Invoice::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/invoices/bulk/delete', [
                'ids' => $invoices->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore invoices', function () {
        $superAdmin = $this->superAdminUser();
        $invoices = Invoice::factory()->count(3)->deleted()->create();
        $ids = $invoices->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/invoices/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('invoices', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoices/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoices/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore invoices', function () {
        $user = $this->normalUser();
        $invoices = Invoice::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/invoices/bulk/restore', [
                'ids' => $invoices->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('send', function () {
    test('authenticated user with permission can send an invoice', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->create(['sent_at' => null]);

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/send")
            ->assertStatus(200);

        $invoice->refresh();

        expect($invoice->sent_at)->not->toBeNull();
    });

    test('user without permission cannot send an invoice', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->create();

        $this->actingAs($user)
            ->postJson("/invoices/{$invoice->id}/send")
            ->assertStatus(403);
    });

    test('send returns 404 for a non-existent invoice', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoices/99999/send')
            ->assertStatus(404);
    });

    test('logs invoice send action with actor id', function () {
        $actor = $this->adminUser();
        $invoice = Invoice::factory()->create(['sent_at' => null]);

        $this->actingAs($actor)
            ->postJson("/invoices/{$invoice->id}/send")
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_SEND_INVOICE)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull();
    });
});

describe('mark as paid', function () {
    test('authenticated user with permission can mark an invoice as paid', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->sent()->create(['paid_at' => null]);

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/mark-as-paid")
            ->assertStatus(200);

        $invoice->refresh();

        expect($invoice->paid_at)->not->toBeNull();
    });

    test('user without permission cannot mark an invoice as paid', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->sent()->create();

        $this->actingAs($user)
            ->postJson("/invoices/{$invoice->id}/mark-as-paid")
            ->assertStatus(403);
    });

    test('mark as paid returns 404 for a non-existent invoice', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoices/99999/mark-as-paid')
            ->assertStatus(404);
    });

    test('logs mark as paid action with actor id', function () {
        $actor = $this->adminUser();
        $invoice = Invoice::factory()->sent()->create(['paid_at' => null]);

        $this->actingAs($actor)
            ->postJson("/invoices/{$invoice->id}/mark-as-paid")
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_MARK_INVOICE_PAID)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull();
    });
});

describe('mark as unpaid', function () {
    test('authenticated user with permission can mark an invoice as unpaid', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->paid()->create();

        $this->actingAs($superAdmin)
            ->postJson("/invoices/{$invoice->id}/mark-as-unpaid")
            ->assertStatus(200);

        $invoice->refresh();

        expect($invoice->paid_at)->toBeNull();
    });

    test('user without permission cannot mark an invoice as unpaid', function () {
        $user = $this->normalUser();
        $invoice = Invoice::factory()->paid()->create();

        $this->actingAs($user)
            ->postJson("/invoices/{$invoice->id}/mark-as-unpaid")
            ->assertStatus(403);
    });

    test('mark as unpaid returns 404 for a non-existent invoice', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/invoices/99999/mark-as-unpaid')
            ->assertStatus(404);
    });

    test('logs mark as unpaid action with actor id', function () {
        $actor = $this->adminUser();
        $invoice = Invoice::factory()->paid()->create();

        $this->actingAs($actor)
            ->postJson("/invoices/{$invoice->id}/mark-as-unpaid")
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_MARK_INVOICE_UNPAID)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull();
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted invoices', function () {
        $superAdmin = $this->superAdminUser();

        Invoice::factory()->count(2)->create();
        $trashed = Invoice::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/invoices')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Invoices/Index')
                ->has('invoices')
            );

        $this->assertSoftDeleted('invoices', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted invoice', function () {
        $superAdmin = $this->superAdminUser();
        $invoice = Invoice::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/invoices/{$invoice->id}")
            ->assertStatus(404);
    });
});

<?php

declare(strict_types=1);

use App\Actions\Quotes\ConvertQuoteToOrder;
use App\Models\Company;
use App\Models\Log;
use App\Models\Order;
use App\Models\Quote;
use App\Models\QuoteStatus;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Services\Quotes\DeleterService;
use App\Services\Quotes\TransitionService;
use App\Services\Quotes\UpdaterService;
// use Database\Seeders\QuoteStatusSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

beforeEach(function () {
    $this->superAdminUser();

    seedQuoteStatusesForCurrentOrganisation();
});

/**
 * Seed the six system quote statuses into the current organisation.
 *
 * QuoteStatusSeeder targets the default organisation, which is not the
 * organisation the tests run in, so the rows are created here instead.
 */
function seedQuoteStatusesForCurrentOrganisation(): void
{
    $organisation = Organisation::current();

    Model::unguarded(function () use ($organisation): void {
        foreach (QuoteStatus::SYSTEM_KEYS as $key) {
            QuoteStatus::withoutGlobalScopes()->firstOrCreate(
                [
                    'system_key' => $key,
                    'organisation_id' => $organisation->id,
                ],
                [
                    'title' => ucfirst($key),
                    'background_colour' => '#e2e8f0',
                    'text_colour' => '#1a202c',
                ],
            );
        }
    });
}

/**
 * Create a quote for a company in the given status.
 *
 * @param  array<string, mixed>  $state
 */
function makeQuote(
    Company $company,
    string $status = QuoteStatus::KEY_DRAFT,
    array $state = [],
): Quote {
    return Quote::factory()
        ->forModel($company)
        ->withStatus($status)
        ->create($state);
}

/**
 * Create a quote that has been sent and accepted, ready to convert.
 */
function makeAcceptedQuote(Company $company, User $actor): Quote
{
    $quote = makeQuote($company);
    $service = app(TransitionService::class);

    $service->send($quote, $actor);
    $service->accept($quote->fresh(), $actor);

    return $quote->fresh();
}

describe('index', function () {
    test('authenticated user with permission can list quotes', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        makeQuote($company);
        makeQuote($company);
        makeQuote($company);

        $this->actingAs($superAdmin)
            ->get('/quotes')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quotes/Index')
                ->has('quotes')
                ->has('statuses')
            );
    });

    test('unauthenticated user cannot list quotes', function () {
        $this->get('/quotes')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list quotes', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/quotes')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/quotes/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quotes/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/quotes/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/quotes/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a draft quote and the total is calculated on the server', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/quotes', [
                'quotable_type' => 'company',
                'quotable_id' => $company->id,
                'title' => 'Server maths',
                'subtotal' => '100.00',
                'discount_amount' => '10.00',
                'tax_amount' => '18.00',
                'currency' => 'GBP',
                // Hostile extras that must be ignored:
                'total_amount' => '1.00',
                'status_id' => QuoteStatus::idForKey(QuoteStatus::KEY_ACCEPTED),
                'converted_order_id' => 1,
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Server maths']);

        $quote = Quote::where('title', 'Server maths')->firstOrFail();

        expect($quote->total_amount)->toBe('108.00')
            ->and($quote->isStatus(QuoteStatus::KEY_DRAFT))->toBeTrue()
            ->and($quote->converted_order_id)->toBeNull();
    });

    test('user without permission cannot create a quote', function () {
        $user = $this->normalUser();
        $company = Company::factory()->create();

        $this->actingAs($user)
            ->postJson('/quotes', [
                'quotable_type' => 'company',
                'quotable_id' => $company->id,
                'title' => 'Blocked quote',
                'subtotal' => '10.00',
                'currency' => 'GBP',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when quotable_type is missing', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/quotes', [
                'quotable_id' => $company->id,
                'title' => 'Missing type',
                'subtotal' => '10.00',
                'currency' => 'GBP',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quotable_type']);
    });

    test('store fails validation when quotable_type is outside the allow-list', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quotes', [
                'quotable_type' => 'App\\Models\\Order',
                'quotable_id' => 1,
                'title' => 'Bad type',
                'subtotal' => '10.00',
                'currency' => 'GBP',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quotable_type']);
    });

    test('store fails validation when quotable_id is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quotes', [
                'quotable_type' => 'company',
                'title' => 'Missing customer',
                'subtotal' => '10.00',
                'currency' => 'GBP',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quotable_id']);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/quotes', [
                'quotable_type' => 'company',
                'quotable_id' => $company->id,
                'subtotal' => '10.00',
                'currency' => 'GBP',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when subtotal is missing', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/quotes', [
                'quotable_type' => 'company',
                'quotable_id' => $company->id,
                'title' => 'Missing subtotal',
                'currency' => 'GBP',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['subtotal']);
    });

    test('store fails validation when currency is not permitted', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/quotes', [
                'quotable_type' => 'company',
                'quotable_id' => $company->id,
                'title' => 'Bad currency',
                'subtotal' => '10.00',
                'currency' => 'XXX',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['currency']);
    });

    test('store fails validation when the discount is larger than the subtotal', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/quotes', [
                'quotable_type' => 'company',
                'quotable_id' => $company->id,
                'title' => 'Too generous',
                'subtotal' => '50.00',
                'discount_amount' => '60.00',
                'currency' => 'GBP',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['discount_amount']);
    });

    test('store fails validation when valid_until is before issued_at', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/quotes', [
                'quotable_type' => 'company',
                'quotable_id' => $company->id,
                'title' => 'Backwards dates',
                'subtotal' => '10.00',
                'currency' => 'GBP',
                'issued_at' => '2026-12-10',
                'valid_until' => '2026-12-01',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['valid_until']);
    });

    test('store fails validation when issued_at is not a valid date', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/quotes', [
                'quotable_type' => 'company',
                'quotable_id' => $company->id,
                'title' => 'Invalid issued_at',
                'subtotal' => '10.00',
                'currency' => 'GBP',
                'issued_at' => 'not-a-date',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['issued_at']);
    });

    test('store fails validation when quotable_id is a user outside the organisation', function () {
        $superAdmin = $this->superAdminUser();
        $outsider = User::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/quotes', [
                'quotable_type' => 'user',
                'quotable_id' => $outsider->id,
                'title' => 'Cross organisation quote',
                'subtotal' => '10.00',
                'currency' => 'GBP',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quotable_id']);

        $this->assertDatabaseMissing('quotes', ['title' => 'Cross organisation quote']);
    });

    test('client-supplied quote_number is ignored and a new one is always generated', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/quotes', [
                'quotable_type' => 'company',
                'quotable_id' => $company->id,
                'quote_number' => 'SPOOFED-0001',
                'title' => 'Attempted quote number spoof',
                'subtotal' => '50.00',
                'currency' => 'GBP',
            ])
            ->assertStatus(201);

        $created = Quote::where('title', 'Attempted quote number spoof')->firstOrFail();

        expect($created->quote_number)
            ->not->toBe('SPOOFED-0001')
            ->and($created->quote_number)->not->toBeEmpty();
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/quotes', [
                'quotable_type' => 'company',
                'quotable_id' => $company->id,
                'title' => 'Minimal quote',
                'subtotal' => '0.00',
                'currency' => 'GBP',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('quotes', [
            'title' => 'Minimal quote',
            'description' => null,
            'notes' => null,
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/quotes', [
                'quotable_type' => 'company',
                'quotable_id' => $company->id,
                'title' => 'Quote with meta',
                'subtotal' => '10.00',
                'currency' => 'GBP',
                'meta' => ['channel' => 'web', 'tags' => ['priority']],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('quotes', ['title' => 'Quote with meta']);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a quote', function () {
        $superAdmin = $this->superAdminUser();
        $quote = makeQuote(Company::factory()->create());

        $this->actingAs($superAdmin)
            ->get("/quotes/{$quote->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quotes/Show')
                ->has('quote')
                ->has('abilities')
            );
    });

    test('unauthenticated user cannot view a quote', function () {
        $quote = makeQuote(Company::factory()->create());

        $this->get("/quotes/{$quote->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a quote', function () {
        $user = $this->userWithNoPermissions();
        $quote = makeQuote(Company::factory()->create());

        $this->actingAs($user)
            ->get("/quotes/{$quote->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent quote', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/quotes/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();
        $quote = makeQuote(Company::factory()->create());

        $this->actingAs($superAdmin)
            ->get("/quotes/{$quote->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quotes/Edit')
                ->has('quote')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $quote = makeQuote(Company::factory()->create());

        $this->get("/quotes/{$quote->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();
        $quote = makeQuote(Company::factory()->create());

        $this->actingAs($user)
            ->get("/quotes/{$quote->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a quote', function () {
        $superAdmin = $this->superAdminUser();
        $quote = makeQuote(Company::factory()->create(), state: ['title' => 'Old title']);

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$quote->id}", ['title' => 'New title'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'New title']);

        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'title' => 'New title',
        ]);
    });

    test('patch verb also updates a quote', function () {
        $superAdmin = $this->superAdminUser();
        $quote = makeQuote(Company::factory()->create(), state: ['notes' => 'Old notes']);

        $this->actingAs($superAdmin)
            ->patchJson("/quotes/{$quote->id}", ['notes' => 'New notes'])
            ->assertStatus(200)
            ->assertJsonFragment(['notes' => 'New notes']);
    });

    test('user without permission cannot update a quote', function () {
        $user = $this->normalUser();
        $quote = makeQuote(Company::factory()->create());

        $this->actingAs($user)
            ->putJson("/quotes/{$quote->id}", ['title' => 'New title'])
            ->assertStatus(403);
    });

    test('quote_number cannot be changed via update', function () {
        $superAdmin = $this->superAdminUser();
        $quote = makeQuote(Company::factory()->create());
        $originalNumber = $quote->quote_number;

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$quote->id}", [
                'quote_number' => 'SPOOFED-0001',
                'title' => 'Renamed quote',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'quote_number' => $originalNumber,
            'title' => 'Renamed quote',
        ]);
    });

    test('total is recalculated on the server when amounts change', function () {
        $superAdmin = $this->superAdminUser();
        $quote = makeQuote(Company::factory()->create());

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$quote->id}", [
                'subtotal' => '200.00',
                'discount_amount' => '20.00',
                'tax_amount' => '36.00',
                'total_amount' => '1.00',
            ])
            ->assertStatus(200);

        expect($quote->fresh()->total_amount)->toBe('216.00');
    });

    test('update fails validation when valid_until is not a valid date', function () {
        $superAdmin = $this->superAdminUser();
        $quote = makeQuote(Company::factory()->create());

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$quote->id}", ['valid_until' => 'not-a-date'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['valid_until']);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();
        $quote = makeQuote(Company::factory()->create(), state: [
            'description' => 'Original description.',
            'notes' => 'Original notes.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/quotes/{$quote->id}", ['title' => 'Updated title'])
            ->assertStatus(200);

        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'title' => 'Updated title',
            'description' => 'Original description.',
            'notes' => 'Original notes.',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();
        $quote = makeQuote(Company::factory()->create(), state: ['notes' => 'Some notes.']);

        $this->actingAs($superAdmin)
            ->patchJson("/quotes/{$quote->id}", ['notes' => null])
            ->assertStatus(200);

        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'notes' => null,
        ]);
    });

    test('logs quote updates with actor id', function () {
        $actor = $this->adminUser();
        $quote = makeQuote(Company::factory()->create(), state: ['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/quotes/{$quote->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_QUOTE)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });

    test('only a draft quote can be edited', function () {
        $admin = $this->adminUser();
        $quote = makeQuote(Company::factory()->create(), state: ['title' => 'Original']);
        app(TransitionService::class)->send($quote, $admin);

        $this->actingAs($admin)
            ->putJson("/quotes/{$quote->id}", ['title' => 'Changed'])
            ->assertStatus(403);

        expect($quote->fresh()->title)->toBe('Original');
    });

    test('editing a sent quote is blocked at service level, covering the Super Admin bypass', function () {
        $admin = $this->adminUser();
        $quote = makeQuote(Company::factory()->create());
        app(TransitionService::class)->send($quote, $admin);

        app(UpdaterService::class)
            ->update($quote->fresh(), ['title' => 'Nope'], $admin->id);
    })->throws(AuthorizationException::class);
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a quote', function () {
        $superAdmin = $this->superAdminUser();
        $quote = makeQuote(Company::factory()->create());

        $this->actingAs($superAdmin)
            ->deleteJson("/quotes/{$quote->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('quotes', ['id' => $quote->id]);
    });

    test('user without permission cannot soft delete a quote', function () {
        $user = $this->normalUser();
        $quote = makeQuote(Company::factory()->create());

        $this->actingAs($user)
            ->deleteJson("/quotes/{$quote->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent quote', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/quotes/99999')
            ->assertStatus(404);
    });

    test('a sent quote cannot be deleted', function () {
        $admin = $this->adminUser();
        $quote = makeQuote(Company::factory()->create());
        app(TransitionService::class)->send($quote, $admin);

        $this->actingAs($admin)
            ->deleteJson("/quotes/{$quote->id}")
            ->assertStatus(403);

        $this->assertNotSoftDeleted('quotes', ['id' => $quote->id]);
    });

    test('deleting a sent quote is blocked at service level', function () {
        $admin = $this->adminUser();
        $quote = makeQuote(Company::factory()->create());
        app(TransitionService::class)->send($quote, $admin);

        app(DeleterService::class)->delete($quote->fresh(), $admin->id);
    })->throws(AuthorizationException::class);
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted quote', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->forModel(Company::factory()->create())->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a quote', function () {
        $user = $this->normalUser();
        $quote = Quote::factory()->forModel(Company::factory()->create())->deleted()->create();

        $this->actingAs($user)
            ->postJson("/quotes/{$quote->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a quote that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $quote = makeQuote(Company::factory()->create());

        $this->actingAs($superAdmin)
            ->postJson("/quotes/{$quote->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a quote', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->forModel(Company::factory()->create())->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/quotes/{$quote->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('quotes', ['id' => $quote->id]);
    });

    test('user without permission cannot force delete a quote', function () {
        $user = $this->normalUser();
        $quote = Quote::factory()->forModel(Company::factory()->create())->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/quotes/{$quote->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a quote that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $quote = makeQuote(Company::factory()->create());

        $this->actingAs($superAdmin)
            ->deleteJson("/quotes/{$quote->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete quotes', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $ids = collect([makeQuote($company), makeQuote($company), makeQuote($company)])
            ->pluck('id')
            ->all();

        $this->actingAs($superAdmin)
            ->postJson('/quotes/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('quotes', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quotes/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quotes/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete quotes', function () {
        $user = $this->normalUser();
        $company = Company::factory()->create();

        $ids = collect([makeQuote($company), makeQuote($company)])->pluck('id')->all();

        $this->actingAs($user)
            ->postJson('/quotes/bulk/delete', ['ids' => $ids])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore quotes', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $quotes = Quote::factory()->count(3)->forModel($company)->deleted()->create();
        $ids = $quotes->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/quotes/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('quotes', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quotes/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quotes/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore quotes', function () {
        $user = $this->normalUser();
        $quotes = Quote::factory()->count(2)->forModel(Company::factory()->create())->deleted()->create();

        $this->actingAs($user)
            ->postJson('/quotes/bulk/restore', ['ids' => $quotes->pluck('id')->all()])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted quotes', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        makeQuote($company);
        makeQuote($company);
        $trashed = Quote::factory()->forModel($company)->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/quotes')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quotes/Index')
                ->has('quotes')
            );

        $this->assertSoftDeleted('quotes', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted quote', function () {
        $superAdmin = $this->superAdminUser();
        $quote = Quote::factory()->forModel(Company::factory()->create())->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/quotes/{$quote->id}")
            ->assertStatus(404);
    });
});

describe('quotable options', function () {
    test('authenticated user with permission can list customer options for an allowed type', function () {
        $superAdmin = $this->superAdminUser();
        Company::factory()->count(2)->create();

        $this->actingAs($superAdmin)
            ->getJson('/quotes/quotable-options/company')
            ->assertStatus(200);
    });

    test('a type outside the allow-list is rejected', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->getJson('/quotes/quotable-options/App%5CModels%5COrder')
            ->assertStatus(404);
    });

    test('user without permission cannot list customer options', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->getJson('/quotes/quotable-options/company')
            ->assertStatus(403);
    });
});

describe('transitions', function () {
    test('a draft quote can be sent over HTTP', function () {
        $admin = $this->adminUser();
        $quote = makeQuote(Company::factory()->create());

        $this->actingAs($admin)
            ->postJson(route('quotes.send', $quote))
            ->assertStatus(200)
            ->assertJson(['id' => $quote->id]);

        expect($quote->fresh()->isStatus(QuoteStatus::KEY_SENT))->toBeTrue();
    });

    test('user without permission cannot send a quote', function () {
        $user = $this->normalUser();
        $quote = makeQuote(Company::factory()->create());

        $this->actingAs($user)
            ->postJson(route('quotes.send', $quote))
            ->assertStatus(403);
    });

    test('a quote needs a future valid until date to be sent', function () {
        $admin = $this->adminUser();
        $quote = makeQuote(Company::factory()->create(), state: ['valid_until' => now()->subDay()]);

        app(TransitionService::class)->send($quote, $admin);
    })->throws(ValidationException::class);

    test('a quote moves through send and accept and records timestamps', function () {
        $admin = $this->adminUser();
        $quote = makeQuote(Company::factory()->create());
        $service = app(TransitionService::class);

        $service->send($quote, $admin);
        $service->accept($quote->fresh(), $admin);

        $quote->refresh();

        expect($quote->isStatus(QuoteStatus::KEY_ACCEPTED))->toBeTrue()
            ->and($quote->sent_at)->not->toBeNull()
            ->and($quote->accepted_at)->not->toBeNull();
    });

    test('a sent quote can be declined over HTTP', function () {
        $admin = $this->adminUser();
        $quote = makeQuote(Company::factory()->create());
        app(TransitionService::class)->send($quote, $admin);

        $this->actingAs($admin)
            ->postJson(route('quotes.decline', $quote))
            ->assertStatus(200);

        expect($quote->fresh()->isStatus(QuoteStatus::KEY_DECLINED))->toBeTrue();
    });

    test('a draft quote cannot be accepted', function () {
        $admin = $this->adminUser();

        app(TransitionService::class)->accept(
            makeQuote(Company::factory()->create()),
            $admin,
        );
    })->throws(ValidationException::class);
});

describe('expiry', function () {
    test('only overdue sent quotes are expired', function () {
        $admin = $this->adminUser();
        $company = Company::factory()->create();
        $service = app(TransitionService::class);

        $overdue = makeQuote($company, state: ['valid_until' => now()->addDay()]);
        $service->send($overdue, $admin);
        $overdue->forceFill(['valid_until' => now()->subHour()])->save();

        $current = makeQuote($company, state: ['valid_until' => now()->addDays(10)]);
        $service->send($current, $admin);

        $expired = $service->expireOverdue($admin);

        expect($expired)->toBe(1)
            ->and($overdue->fresh()->isStatus(QuoteStatus::KEY_EXPIRED))->toBeTrue()
            ->and($current->fresh()->isStatus(QuoteStatus::KEY_SENT))->toBeTrue();
    });
});

describe('convert', function () {
    test('an accepted quote converts into exactly one order using the short registry key', function () {
        $admin = $this->adminUser();
        $company = Company::factory()->create();
        $quote = makeAcceptedQuote($company, $admin);

        $order = app(ConvertQuoteToOrder::class)->handle($quote, $admin);

        expect($order)->toBeInstanceOf(Order::class)
            ->and($order->orderable_type)->toBe(Company::class)
            ->and($order->total_amount)->toBe($quote->total_amount)
            ->and($quote->fresh()->isStatus(QuoteStatus::KEY_CONVERTED))->toBeTrue()
            ->and($quote->fresh()->converted_order_id)->toBe($order->id);

        // A second conversion must fail and must not create a second order.
        expect(fn () => app(ConvertQuoteToOrder::class)->handle($quote->fresh(), $admin))
            ->toThrow(ValidationException::class);

        expect(Order::count())->toBe(1);
    });

    test('authenticated user with permission can convert a quote over HTTP', function () {
        $admin = $this->adminUser();
        $quote = makeAcceptedQuote(Company::factory()->create(), $admin);

        $response = $this->actingAs($admin)
            ->postJson(route('quotes.convert', $quote))
            ->assertStatus(201);

        $this->assertDatabaseHas('orders', ['id' => $response->json('order_id')]);
    });

    test('a quote that has not been accepted cannot be converted', function () {
        $admin = $this->adminUser();

        app(ConvertQuoteToOrder::class)->handle(
            makeQuote(Company::factory()->create()),
            $admin,
        );
    })->throws(ValidationException::class);

    test('user without permission cannot convert a quote', function () {
        $admin = $this->adminUser();
        $user = $this->normalUser();
        $quote = makeAcceptedQuote(Company::factory()->create(), $admin);

        $this->actingAs($user)
            ->postJson(route('quotes.convert', $quote))
            ->assertStatus(403);

        expect(Order::count())->toBe(0);
    });
});

describe('import', function () {
    test('quotes are imported as drafts and status and total columns are ignored', function () {
        $admin = $this->adminUser();
        $company = Company::factory()->create();

        $csv = "quotable_type,quotable_id,title,subtotal,tax_amount,total_amount,status_id\n"
            ."company,{$company->id},Imported,100.00,20.00,999.00,"
            .QuoteStatus::idForKey(QuoteStatus::KEY_ACCEPTED)."\n";

        $file = UploadedFile::fake()->createWithContent('quotes.csv', $csv);

        $preview = $this->actingAs($admin)
            ->postJson(route('quotes.import.preview'), ['file' => $file])
            ->assertOk()
            ->json();

        $this->actingAs($admin)
            ->postJson(route('quotes.import.commit'), ['token' => $preview['token']])
            ->assertOk();

        $quote = Quote::where('title', 'Imported')->firstOrFail();

        expect($quote->total_amount)->toBe('120.00')
            ->and($quote->isStatus(QuoteStatus::KEY_DRAFT))->toBeTrue();
    });

    test('user without permission cannot preview an import', function () {
        $user = $this->normalUser();

        $file = UploadedFile::fake()->createWithContent('quotes.csv', "quotable_type\n");

        $this->actingAs($user)
            ->postJson(route('quotes.import.preview'), ['file' => $file])
            ->assertStatus(403);
    });

    test('commit fails validation when the token is not a uuid', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->postJson(route('quotes.import.commit'), ['token' => 'not-a-uuid'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    });
});

describe('export', function () {
    test('exports do not expose formula injection', function () {
        $admin = $this->adminUser();

        makeQuote(Company::factory()->create(), state: ['title' => '=HYPERLINK("http://evil")']);

        $response = $this->actingAs($admin)->get(route('quotes.export'));

        $response->assertOk();
        expect($response->streamedContent())->toContain("'=HYPERLINK");
    });

    test('user without permission cannot export quotes', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get(route('quotes.export'))
            ->assertStatus(403);
    });
});
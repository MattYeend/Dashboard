<?php

declare(strict_types=1);

use App\Actions\Payments\RecordPayment;
use App\Actions\Payments\RefundPayment;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Log;
use App\Models\Organisation;
use App\Models\Payment;
use App\Models\User;
use App\Services\Invoices\BalanceService;
use App\Services\Payments\DataPreparationService;
use App\Services\Payments\DeleterService;
use App\Services\Payments\TransitionService;
use App\Services\Payments\UpdaterService;
use Database\Seeders\PaymentStatusSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

/**
 * Make sure the system statuses exist for the current organisation.
 *
 * Both seeders are safe to run more than once.
 */
function seedPaymentStatuses(): void
{
    $organisation = Organisation::current();

    app(PaymentStatusSeeder::class)->seedForOrganisation($organisation);

    Model::unguarded(function () use ($organisation): void {
        foreach (['Draft', 'Sent', 'Partially Paid', 'Paid', 'Overdue'] as $title) {
            InvoiceStatus::withoutGlobalScopes()->firstOrCreate(
                [
                    'title' => $title,
                    'organisation_id' => $organisation->id,
                ],
                [
                    'background_colour' => '#e2e8f0',
                    'text_colour' => '#1a202c',
                ],
            );
        }
    });
}

/**
 * Create an invoice in the given status with the given total in pounds.
 */
function paymentInvoice(string $status, string $total): Invoice
{
    seedPaymentStatuses();

    return Invoice::factory()->create([
        'status_id' => InvoiceStatus::where('title', $status)->firstOrFail()->id,
        'subtotal' => $pence = (int) bcmul($total, '100', 0),
        'tax_total' => 0,
        'total' => $pence,
    ]);
}

/**
 * Build the request data for recording a payment.
 *
 * @return array<string, mixed>
 */
function paymentPayload(Invoice $invoice, string $amount, ?string $reference = null): array
{
    return [
        'invoice_id' => $invoice->id,
        'method' => 'bank_transfer',
        'amount' => $amount,
        'paid_at' => now()->subHour()->toDateTimeString(),
        'reference' => $reference,
        'notes' => null,
    ];
}

/**
 * Record a payment through the action, as the form does.
 *
 * @param  array<string, mixed>  $data
 */
function recordPaymentAs(array $data, User $actor): Payment
{
    return app(RecordPayment::class)->handle(
        app(DataPreparationService::class)->prepareForCreation($data),
        $actor,
    );
}

/**
 * Refund through the action, as the controller does.
 */
function refundPaymentAs(
    Payment $payment,
    string $amount,
    User $actor,
    ?string $reference = null,
): Payment {
    return app(RefundPayment::class)->handle(
        $payment,
        app(DataPreparationService::class)->prepareForRefund([
            'amount' => $amount,
            'method' => 'bank_transfer',
            'paid_at' => now()->toDateTimeString(),
            'reference' => $reference,
            'notes' => 'Customer request',
        ]),
        $actor,
    );
}

/**
 * Record a full payment of £100.00 against a new Sent invoice.
 *
 * @return array{0: Invoice, 1: Payment}
 */
function paidInvoice(User $actor): array
{
    $invoice = paymentInvoice('Sent', '100.00');
    $payment = recordPaymentAs(paymentPayload($invoice, '100.00', 'P-1'), $actor);

    return [$invoice, $payment];
}

describe('record', function () {
    test('marks an invoice partially paid, then paid', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');

        recordPaymentAs(paymentPayload($invoice, '40.00', 'A-1'), $admin);

        expect($invoice->refresh()->amount_paid)->toBe(4000)
            ->and($invoice->status->title)->toBe('Partially Paid')
            ->and($invoice->paid_at)->toBeNull();

        recordPaymentAs(paymentPayload($invoice, '60.00', 'A-2'), $admin);

        expect($invoice->refresh()->amount_paid)->toBe(10000)
            ->and($invoice->status->title)->toBe('Paid')
            ->and($invoice->paid_at)->not->toBeNull();
    });

    test('rejects an overpayment', function () {
        $admin = $this->adminUser();

        recordPaymentAs(
            paymentPayload(paymentInvoice('Sent', '100.00'), '100.01'),
            $admin,
        );
    })->throws(ValidationException::class, 'exceeds the outstanding balance');

    test('rejects a payment that would overpay after an earlier one', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');

        recordPaymentAs(paymentPayload($invoice, '70.00', 'B-1'), $admin);
        recordPaymentAs(paymentPayload($invoice, '30.01', 'B-2'), $admin);
    })->throws(ValidationException::class);

    test('does not trust a drifted amount_paid column when checking the balance', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');
        $invoice->forceFill(['amount_paid' => '0'])->save();

        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => '90.00']);

        recordPaymentAs(paymentPayload($invoice, '20.00'), $admin);
    })->throws(ValidationException::class, 'exceeds the outstanding balance');

    test('rejects a payment against a draft invoice', function () {
        $admin = $this->adminUser();

        recordPaymentAs(
            paymentPayload(paymentInvoice('Draft', '100.00'), '10.00'),
            $admin,
        );
    })->throws(ValidationException::class);

    test('rejects a payment against a paid invoice', function () {
        $admin = $this->adminUser();

        recordPaymentAs(
            paymentPayload(paymentInvoice('Paid', '100.00'), '10.00'),
            $admin,
        );
    })->throws(ValidationException::class);

    test('rejects a duplicate reference on the same invoice', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');

        recordPaymentAs(paymentPayload($invoice, '10.00', 'DUP'), $admin);

        $this->actingAs($admin)
            ->post(route('payments.store'), paymentPayload($invoice, '10.00', 'DUP'))
            ->assertSessionHasErrors('reference');
    });

    test('ignores a client supplied type, status or currency', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');

        $this->actingAs($admin)
            ->post(route('payments.store'), paymentPayload($invoice, '10.00') + [
                'type' => 'refund',
                'status_id' => 999,
                'currency' => 'USD',
            ])
            ->assertRedirect();

        $payment = Payment::query()->firstOrFail();

        expect($payment->type->value)->toBe('payment')
            ->and($payment->currency)->toBe('GBP')
            ->and($payment->isCompleted())->toBeTrue();
    });

    test('rejects a future date and a three decimal amount', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');

        $this->actingAs($admin)
            ->post(route('payments.store'), array_merge(paymentPayload($invoice, '10.001'), [
                'paid_at' => now()->addDay()->toDateTimeString(),
            ]))
            ->assertSessionHasErrors(['amount', 'paid_at']);
    });

    test('forbids a user without permission from recording a payment', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->post(route('payments.store'), paymentPayload(paymentInvoice('Sent', '100.00'), '10.00'))
            ->assertForbidden();
    });

    test('cannot record a payment against an invoice from another organisation', function () {
        $admin = $this->adminUser();
        seedPaymentStatuses();

        $other = $this->inOrganisation(
            $this->otherOrganisation(),
            fn () => Invoice::factory()->create([
                'status_id' => InvoiceStatus::factory()->create(['title' => 'Sent'])->id,
                'subtotal' => 10000,
                'tax_total' => 0,
                'total' => 10000,
            ]),
        );

        $this->actingAs($admin)
            ->post(route('payments.store'), paymentPayload($other, '10.00'))
            ->assertSessionHasErrors('invoice_id');
    });

    test('records an audit entry for the payment and the recalculation', function () {
        $admin = $this->adminUser();

        recordPaymentAs(
            paymentPayload(paymentInvoice('Sent', '100.00'), '10.00'),
            $admin,
        );

        expect(Log::where('action_id', Log::ACTION_CREATE_PAYMENT)->exists())->toBeTrue()
            ->and(Log::where('action_id', Log::ACTION_RECALCULATE_INVOICE_BALANCE)->exists())->toBeTrue();
    });
});

describe('refund', function () {
    test('puts a fully refunded invoice back to Sent with nothing paid', function () {
        $admin = $this->adminUser();
        [$invoice, $payment] = paidInvoice($admin);

        refundPaymentAs($payment, '100.00', $admin);

        expect($invoice->refresh()->amount_paid)->toBe(0)
            ->and($invoice->status->title)->toBe('Sent')
            ->and($invoice->paid_at)->toBeNull();
    });

    test('makes a partly refunded paid invoice Partially Paid', function () {
        $admin = $this->adminUser();
        [$invoice, $payment] = paidInvoice($admin);

        refundPaymentAs($payment, '25.00', $admin);

        expect($invoice->refresh()->amount_paid)->toBe(7500)
            ->and($invoice->status->title)->toBe('Partially Paid');
    });

    test('rejects a refund larger than the unrefunded amount', function () {
        $admin = $this->adminUser();
        [, $payment] = paidInvoice($admin);

        refundPaymentAs($payment, '100.01', $admin);
    })->throws(ValidationException::class);

    test('rejects a second refund that takes the total past the payment', function () {
        $admin = $this->adminUser();
        [, $payment] = paidInvoice($admin);

        refundPaymentAs($payment, '60.00', $admin, 'R-1');
        refundPaymentAs($payment, '40.01', $admin, 'R-2');
    })->throws(ValidationException::class);

    test('rejects refunding a refund', function () {
        $admin = $this->adminUser();
        [, $payment] = paidInvoice($admin);

        $refund = refundPaymentAs($payment, '10.00', $admin);

        refundPaymentAs($refund, '5.00', $admin);
    })->throws(ValidationException::class);

    test('rejects refunding a pending payment', function () {
        $admin = $this->adminUser();
        [$invoice] = paidInvoice($admin);
        $pending = Payment::factory()->pending()->create(['invoice_id' => $invoice->id]);

        refundPaymentAs($pending, '5.00', $admin);
    })->throws(ValidationException::class);

    test('rejects a refund dated before the payment', function () {
        $admin = $this->adminUser();
        [, $payment] = paidInvoice($admin);

        app(RefundPayment::class)->handle(
            $payment,
            app(DataPreparationService::class)->prepareForRefund([
                'amount' => '5.00',
                'method' => 'bank_transfer',
                'paid_at' => now()->subDays(30)->toDateTimeString(),
                'notes' => 'Backdated',
            ]),
            $admin,
        );
    })->throws(ValidationException::class);

    test('requires a reason for a refund', function () {
        $admin = $this->adminUser();
        [, $payment] = paidInvoice($admin);

        $this->actingAs($admin)
            ->post(route('payments.refund', $payment), [
                'amount' => '5.00',
                'method' => 'bank_transfer',
                'paid_at' => now()->toDateTimeString(),
                'notes' => '',
            ])
            ->assertSessionHasErrors('notes');
    });

    test('forbids a user without the refund permission', function () {
        $admin = $this->adminUser();
        [, $payment] = paidInvoice($admin);

        $editor = $this->normalUser();
        $editor->givePermissionTo(['view any payment', 'view payment', 'create payment']);

        $this->actingAs($editor)
            ->post(route('payments.refund', $payment), [
                'amount' => '5.00',
                'method' => 'bank_transfer',
                'paid_at' => now()->toDateTimeString(),
                'notes' => 'Reason',
            ])
            ->assertForbidden();
    });
});

describe('immutability', function () {
    test('refuses to edit a completed payment through the route', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');
        $payment = Payment::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($admin)
            ->put(route('payments.update', $payment), [
                'method' => 'cash',
                'paid_at' => now()->toDateTimeString(),
            ])
            ->assertForbidden();
    });

    test('refuses to delete a completed payment through the route', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');
        $payment = Payment::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($admin)
            ->delete(route('payments.destroy', $payment))
            ->assertForbidden();

        expect($payment->fresh()->trashed())->toBeFalse();
    });

    test('blocks editing and deleting a completed payment at service level, covering the Super Admin bypass', function () {
        $super = $this->superAdminUser();
        $invoice = paymentInvoice('Sent', '100.00');
        $payment = Payment::factory()->create(['invoice_id' => $invoice->id]);

        expect(fn () => app(UpdaterService::class)->update($payment, ['method' => 'cash'], $super->id))
            ->toThrow(AuthorizationException::class);

        expect(fn () => app(DeleterService::class)->delete($payment, $super->id))
            ->toThrow(AuthorizationException::class);
    });

    test('lets a Super Admin reach the route but still be stopped by the service', function () {
        $super = $this->superAdminUser();
        $invoice = paymentInvoice('Sent', '100.00');
        $payment = Payment::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($super)
            ->put(route('payments.update', $payment), [
                'method' => 'cash',
                'paid_at' => now()->toDateTimeString(),
            ])
            ->assertForbidden();

        expect($payment->fresh()->method->value)->toBe('bank_transfer');
    });

    test('never lets a completed refund be edited or deleted', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');
        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => '50.00',
        ]);
        $refund = Payment::factory()->refundOf($payment, '10.00')->create();

        expect(fn () => app(DeleterService::class)->delete($refund, $admin->id))
            ->toThrow(AuthorizationException::class);
    });

    test('edits only method, date, reference and notes of a pending payment', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');
        $payment = Payment::factory()->pending()->create([
            'invoice_id' => $invoice->id,
            'amount' => '10.00',
        ]);

        $this->actingAs($admin)
            ->put(route('payments.update', $payment), [
                'method' => 'cash',
                'paid_at' => now()->subHour()->toDateTimeString(),
                'reference' => 'EDITED',
                'amount' => '99.00',
                'invoice_id' => 12345,
            ])
            ->assertRedirect();

        $fresh = $payment->fresh();

        expect($fresh->method->value)->toBe('cash')
            ->and($fresh->reference)->toBe('EDITED')
            ->and($fresh->amount)->toBe('10.00')
            ->and($fresh->invoice_id)->toBe($invoice->id);
    });

    test('deletes and restores a pending payment without touching the balance', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');
        $payment = Payment::factory()->pending()->create([
            'invoice_id' => $invoice->id,
            'amount' => '10.00',
        ]);

        $this->actingAs($admin)
            ->delete(route('payments.destroy', $payment))
            ->assertRedirect();

        expect($payment->fresh()->trashed())->toBeTrue()
            ->and($invoice->fresh()->amount_paid)->toBe(0);

        $this->actingAs($admin)
            ->post(route('payments.restore', $payment->id))
            ->assertRedirect();

        expect($payment->fresh()->trashed())->toBeFalse();
    });

    test('skips locked payments in a bulk delete and reports them', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');
        $locked = Payment::factory()->create(['invoice_id' => $invoice->id]);
        $pending = Payment::factory()->pending()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($admin)
            ->postJson(route('payments.bulk.delete'), ['ids' => [$locked->id, $pending->id]])
            ->assertOk()
            ->assertJsonFragment(['skipped' => [$locked->id]]);

        expect($locked->fresh()->trashed())->toBeFalse()
            ->and($pending->fresh()->trashed())->toBeTrue();
    });

    test('cannot force delete a live payment', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');
        $payment = Payment::factory()->pending()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($admin)
            ->delete(route('payments.force-delete', $payment->id))
            ->assertNotFound();
    });

    test('refuses to delete an invoice that has payments', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');
        Payment::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($admin)
            ->delete(route('invoices.destroy', $invoice))
            ->assertSessionHasErrors('invoice');
    });
});

describe('balance service', function () {
    test('counts only completed rows', function () {
        $invoice = paymentInvoice('Sent', '100.00');

        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => '30.00']);
        Payment::factory()->pending()->create(['invoice_id' => $invoice->id, 'amount' => '40.00']);
        Payment::factory()->failed()->create(['invoice_id' => $invoice->id, 'amount' => '50.00']);

        expect(app(BalanceService::class)->netPaid($invoice))->toBe('30.00');
    });

    test('ignores a soft-deleted pending payment', function () {
        $invoice = paymentInvoice('Sent', '100.00');
        $pending = Payment::factory()->pending()->create([
            'invoice_id' => $invoice->id,
            'amount' => '40.00',
        ]);
        $pending->delete();

        expect(app(BalanceService::class)->balanceDue($invoice))->toBe('100.00');
    });

    test('is idempotent and audits each run', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');
        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => '100.00']);

        DB::transaction(function () use ($invoice, $admin) {
            app(BalanceService::class)->recalculate($invoice, $admin);
            app(BalanceService::class)->recalculate($invoice->refresh(), $admin);
        });

        expect($invoice->refresh()->amount_paid)->toBe(10000)
            ->and($invoice->status->title)->toBe('Paid')
            ->and(Log::where('action_id', Log::ACTION_RECALCULATE_INVOICE_BALANCE)->count())->toBe(2);
    });

    test('keeps an overdue invoice overdue when nothing is paid', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Overdue', '100.00');

        DB::transaction(fn () => app(BalanceService::class)->recalculate($invoice, $admin));

        expect($invoice->refresh()->status->title)->toBe('Overdue');
    });

    test('never changes the status of a draft invoice', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Draft', '100.00');

        DB::transaction(fn () => app(BalanceService::class)->recalculate($invoice, $admin));

        expect($invoice->refresh()->status->title)->toBe('Draft');
    });

    test('uses exact decimal maths', function () {
        $invoice = paymentInvoice('Sent', '0.30');

        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => '0.10']);
        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => '0.20']);

        expect(app(BalanceService::class)->balanceDue($invoice))->toBe('0.00');
    });

    test('refuses to recalculate outside a transaction', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '10.00');

        // The test itself runs inside a transaction, so stub the level the guard reads.
        DB::partialMock()->shouldReceive('transactionLevel')->andReturn(0);

        expect(fn () => app(BalanceService::class)->recalculate($invoice, $admin))
            ->toThrow(LogicException::class);
    });

    test('converts invoice pence to pounds and back', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '1440.00');

        expect($invoice->total)->toBe(144000)
            ->and(app(BalanceService::class)->poundsFromPence($invoice->total))->toBe('1440.00')
            ->and(app(BalanceService::class)->balanceDue($invoice))->toBe('1440.00');

        recordPaymentAs(paymentPayload($invoice, '1440.00', 'PENCE-1'), $admin);

        expect($invoice->refresh()->amount_paid)->toBe(144000)
            ->and($invoice->status->title)->toBe('Paid');
    });
});

describe('transitions', function () {
    test('completes a pending payment and applies it to the invoice', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');
        $payment = Payment::factory()->pending()->create([
            'invoice_id' => $invoice->id,
            'amount' => '100.00',
        ]);

        app(TransitionService::class)->complete($payment, $admin);

        expect($payment->fresh()->isCompleted())->toBeTrue()
            ->and($invoice->refresh()->status->title)->toBe('Paid');
    });

    test('refuses to complete a payment that would overpay', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');

        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => '80.00']);
        $pending = Payment::factory()->pending()->create([
            'invoice_id' => $invoice->id,
            'amount' => '30.00',
        ]);

        app(TransitionService::class)->complete($pending, $admin);
    })->throws(ValidationException::class);

    test('fails and cancels a pending payment without touching the invoice', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');
        $failed = Payment::factory()->pending()->create(['invoice_id' => $invoice->id]);
        $cancelled = Payment::factory()->pending()->create(['invoice_id' => $invoice->id]);

        app(TransitionService::class)->fail($failed, $admin);
        app(TransitionService::class)->cancel($cancelled, $admin);

        expect($failed->fresh()->isStatus('failed'))->toBeTrue()
            ->and($cancelled->fresh()->isStatus('cancelled'))->toBeTrue()
            ->and($invoice->fresh()->amount_paid)->toBe(0);
    });

    test('only moves a pending payment', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');
        $completed = Payment::factory()->create(['invoice_id' => $invoice->id]);

        app(TransitionService::class)->cancel($completed, $admin);
    })->throws(ValidationException::class);
});

describe('import', function () {
    test('imports valid rows through the same rules as the form and skips the rest', function () {
        Storage::fake();

        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');

        $csv = "invoice_number,amount,method,paid_at,reference,status,type\n"
            .$invoice->invoice_number.",40.00,bank_transfer,2026-10-01,IMP-1,pending,refund\n"
            .$invoice->invoice_number.",50.00,bank_transfer,2026-10-02,IMP-2,,\n"
            ."NOPE-1,10.00,bank_transfer,2026-10-02,IMP-3,,\n"
            .$invoice->invoice_number.",10.00,cheque,2099-01-01,IMP-4,,\n";

        $file = UploadedFile::fake()->createWithContent('payments.csv', $csv);

        $preview = $this->actingAs($admin)
            ->postJson(route('payments.import.preview'), ['file' => $file])
            ->assertOk()
            ->json();

        $this->actingAs($admin)
            ->postJson(route('payments.import.commit'), ['token' => $preview['token']])
            ->assertOk();

        $first = Payment::where('reference', 'IMP-1')->firstOrFail();

        expect($first->type->value)->toBe('payment')
            ->and($first->isCompleted())->toBeTrue()
            ->and(Payment::where('reference', 'IMP-2')->exists())->toBeTrue()
            ->and(Payment::where('reference', 'IMP-3')->exists())->toBeFalse()
            ->and(Payment::where('reference', 'IMP-4')->exists())->toBeFalse();
    });

    test('forbids a manager from importing', function () {
        $manager = $this->normalUser();
        $manager->givePermissionTo(['view any payment', 'view payment']);

        $this->actingAs($manager)
            ->postJson(route('payments.import.preview'), [
                'file' => UploadedFile::fake()->createWithContent('payments.csv', "invoice_number\n"),
            ])
            ->assertForbidden();
    });

    test('commit fails validation when the token is not a uuid', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->postJson(route('payments.import.commit'), ['token' => 'not-a-uuid'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    });
});

describe('export', function () {
    test('exports without formula injection', function () {
        $admin = $this->adminUser();
        $invoice = paymentInvoice('Sent', '100.00');

        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'reference' => '=HYPERLINK("http://x")',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('payments.export'))
            ->assertOk();

        expect($response->streamedContent())->toContain("'=HYPERLINK");
    });

    test('user without permission cannot export payments', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get(route('payments.export'))
            ->assertForbidden();
    });
});

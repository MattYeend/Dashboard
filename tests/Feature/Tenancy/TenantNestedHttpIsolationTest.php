<?php

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Organisation;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Tests\Concerns\ActsAsOrganisationMember;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(
    LazilyRefreshDatabase::class, 
    ActsAsOrganisationMember::class
);

beforeEach(function () {
    $this->setUpOrganisationIsolation();
});

afterEach(function () {
    Organisation::forgetCurrent();
});

describe('invoice items', function () {
    test(
        'returns 404 when another organisation\'s invoice item is requested',
        function () {
            $invoice = $this->organisationA->execute(
                fn () => Invoice::factory()->create(),
            );

            $item = $this->organisationA->execute(
                fn () => InvoiceItem::factory()->create([
                    'invoice_id' => $invoice->id,
                ]),
            );

            $userB = $this->memberOf($this->organisationB);

            $responses = [
                'show' => $this
                    ->actingAsMemberOf(
                        $this->organisationB,
                        $userB,
                    )
                    ->get(
                        route(
                            'invoices.items.show',
                            [$invoice->id, $item->id],
                        ),
                    )
                    ->status(),

                'edit' => $this
                    ->actingAsMemberOf(
                        $this->organisationB,
                        $userB,
                    )
                    ->get(
                        route(
                            'invoices.items.edit',
                            [$invoice->id, $item->id],
                        ),
                    )
                    ->status(),

                'update' => $this
                    ->actingAsMemberOf(
                        $this->organisationB,
                        $userB,
                    )
                    ->put(
                        route(
                            'invoices.items.update',
                            [$invoice->id, $item->id],
                        ),
                        [],
                    )
                    ->status(),

                'destroy' => $this
                    ->actingAsMemberOf(
                        $this->organisationB,
                        $userB,
                    )
                    ->delete(
                        route(
                            'invoices.items.destroy',
                            [$invoice->id, $item->id],
                        ),
                    )
                    ->status(),
            ];

            expect($responses)->toBe([
                'show' => 404,
                'edit' => 404,
                'update' => 404,
                'destroy' => 404,
            ]);

            expect(
                InvoiceItem::withoutGlobalScopes()
                    ->whereKey($item->id)
                    ->exists(),
            )->toBeTrue();
        },
    );

    test(
        'does not bulk delete another organisation\'s invoice items',
        function () {
            $invoice = $this->organisationA->execute(
                fn () => Invoice::factory()->create(),
            );

            $item = $this->organisationA->execute(
                fn () => InvoiceItem::factory()->create([
                    'invoice_id' => $invoice->id,
                ]),
            );

            $userB = $this->memberOf($this->organisationB);

            $this
                ->actingAsMemberOf(
                    $this->organisationB,
                    $userB,
                )
                ->post(
                    route(
                        'invoices.items.bulk.delete',
                        $invoice->id,
                    ),
                    [
                        'ids' => [$item->id],
                    ],
                );

            expect(
                InvoiceItem::withoutGlobalScopes()
                    ->whereKey($item->id)
                    ->whereNull('deleted_at')
                    ->exists(),
            )->toBeTrue();
        },
    );

    test(
        'cannot even reach the invoice item through its own invoice from a different organisation',
        function () {
            $invoice = $this->organisationA->execute(
                fn () => Invoice::factory()->create(),
            );

            $item = $this->organisationA->execute(
                fn () => InvoiceItem::factory()->create([
                    'invoice_id' => $invoice->id,
                ]),
            );

            $userB = $this->memberOf($this->organisationB);

            // The parent invoice itself should already 404 for organisation B,
            // independent of the item route - confirms the leak isn't only
            // caught at the child level.
            $this
                ->actingAsMemberOf(
                    $this->organisationB,
                    $userB,
                )
                ->get(
                    route(
                        'invoices.show',
                        $invoice->id,
                    ),
                )
                ->assertStatus(404);
        },
    );
});

describe('pipeline stages', function () {
    test(
        'returns 404 when another organisation\'s pipeline stage is requested',
        function () {
            $pipeline = $this->organisationA->execute(
                fn () => Pipeline::factory()->create(),
            );

            $stage = $this->organisationA->execute(
                fn () => PipelineStage::factory()->create([
                    'pipeline_id' => $pipeline->id,
                ]),
            );

            $userB = $this->memberOf($this->organisationB);

            $responses = [
                'show' => $this
                    ->actingAsMemberOf(
                        $this->organisationB,
                        $userB,
                    )
                    ->get(
                        route(
                            'pipelines.stages.show',
                            [$pipeline->id, $stage->id],
                        ),
                    )
                    ->status(),

                'edit' => $this
                    ->actingAsMemberOf(
                        $this->organisationB,
                        $userB,
                    )
                    ->get(
                        route(
                            'pipelines.stages.edit',
                            [$pipeline->id, $stage->id],
                        ),
                    )
                    ->status(),

                'update' => $this
                    ->actingAsMemberOf(
                        $this->organisationB,
                        $userB,
                    )
                    ->put(
                        route(
                            'pipelines.stages.update',
                            [$pipeline->id, $stage->id],
                        ),
                        [],
                    )
                    ->status(),

                'destroy' => $this
                    ->actingAsMemberOf(
                        $this->organisationB,
                        $userB,
                    )
                    ->delete(
                        route(
                            'pipelines.stages.destroy',
                            [$pipeline->id, $stage->id],
                        ),
                    )
                    ->status(),
            ];

            expect($responses)->toBe([
                'show' => 404,
                'edit' => 404,
                'update' => 404,
                'destroy' => 404,
            ]);

            expect(
                PipelineStage::withoutGlobalScopes()
                    ->whereKey($stage->id)
                    ->exists(),
            )->toBeTrue();
        },
    );

    test(
        'does not bulk delete another organisation\'s pipeline stages',
        function () {
            $pipeline = $this->organisationA->execute(
                fn () => Pipeline::factory()->create(),
            );

            $stage = $this->organisationA->execute(
                fn () => PipelineStage::factory()->create([
                    'pipeline_id' => $pipeline->id,
                ]),
            );

            $userB = $this->memberOf($this->organisationB);

            $this
                ->actingAsMemberOf(
                    $this->organisationB,
                    $userB,
                )
                ->post(
                    route(
                        'pipelines.stages.bulk.delete',
                        $pipeline->id,
                    ),
                    [
                        'ids' => [$stage->id],
                    ],
                );

            expect(
                PipelineStage::withoutGlobalScopes()
                    ->whereKey($stage->id)
                    ->whereNull('deleted_at')
                    ->exists(),
            )->toBeTrue();
        },
    );

    test(
        'cannot even reach the pipeline stage through its own pipeline from a different organisation',
        function () {
            $pipeline = $this->organisationA->execute(
                fn () => Pipeline::factory()->create(),
            );

            $userB = $this->memberOf($this->organisationB);

            $this
                ->actingAsMemberOf(
                    $this->organisationB,
                    $userB,
                )
                ->get(
                    route(
                        'pipelines.show',
                        $pipeline->id,
                    ),
                )
                ->assertStatus(404);
        },
    );
});

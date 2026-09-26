<?php

use App\Models\Activity;
use App\Models\Comment;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\DealStatus;
use App\Models\Industry;
use App\Models\InteractionLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceStatus;
use App\Models\Label;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Organisation;
use App\Models\OrganisationDataExport;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\PipelineStatus;
use App\Models\Post;
use App\Models\Report;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Tests\Concerns\ActsAsOrganisationMember;

uses(ActsAsOrganisationMember::class);

/**
 * Route prefix to model, for resources with full show/edit/update/destroy
 * routes.
 *
 * @return array<string, class-string>
 */
function tenantResourceMap(): array
{
    return [
        'contacts' => Contact::class,
        'companies' => Company::class,
        'task-statuses' => TaskStatus::class,
        'tasks' => Task::class,
        'order-statuses' => OrderStatus::class,
        'orders' => Order::class,
        'addresses' => \App\Models\Address::class,
        'categories' => \App\Models\Category::class,
        'posts' => Post::class,
        'invoice-statuses' => InvoiceStatus::class,
        'deal-statuses' => DealStatus::class,
        'deals' => Deal::class,
        'ticket-statuses' => TicketStatus::class,
        'ticket-priorities' => TicketPriority::class,
        'labels' => Label::class,
        'tickets' => Ticket::class,
        'reports' => Report::class,
        'invoices' => Invoice::class,
        'pipelines' => Pipeline::class,
        'pipeline-statuses' => PipelineStatus::class,
        'industries' => Industry::class,
    ];
}

/**
 * Route prefix to model, for resources missing one or more of
 * show/edit/update/destroy.
 *
 * @return array<string, array{
 *     model: class-string,
 *     actions: list<string>
 * }>
 */
function tenantPartialResourceMap(): array
{
    return [
        'comments' => [
            'model' => Comment::class,
            'actions' => ['show', 'update', 'destroy'],
        ],
        'activities' => [
            'model' => Activity::class,
            'actions' => ['update', 'destroy'],
        ],
        'interaction-logs' => [
            'model' => InteractionLog::class,
            'actions' => ['update', 'destroy'],
        ],
    ];
}

/**
 * Route prefix to model, for the bulk-delete check.
 *
 * Includes all full resources plus the partial resources
 * that also carry bulk routes.
 *
 * InteractionLog has no bulk route.
 *
 * @return array<string, class-string>
 */
function tenantBulkResourceMap(): array
{
    return tenantResourceMap() + [
        'comments' => Comment::class,
        'activities' => Activity::class,
    ];
}

beforeEach(function () {
    $this->setUpOrganisationIsolation();
});

afterEach(function () {
    Organisation::forgetCurrent();
});

describe('resource coverage', function () {
    test('maps every scoped model to a tested resource or explicitly opts out', function () {
        $optOut = [
            InvoiceItem::class => 'nested under its parent invoice (scoped route bindings, e.g. invoices/{invoice}/items/{invoiceItem}) — covered by TenantNestedHttpIsolationTest.php instead',

            PipelineStage::class => 'nested under its parent pipeline (scoped route bindings, e.g. pipelines/{pipeline}/stages/{stage}) — covered by TenantNestedHttpIsolationTest.php instead',

            OrganisationDataExport::class => 'only route is a nested file download (organisations.data-privacy.download) — needs its own download-scoped test once BelongsToOrganisation is confirmed working on it',
        ];

        $partialModels = collect(tenantPartialResourceMap())
            ->pluck('model')
            ->all();

        $untested = collect(config('organisations.scoped_models'))
            ->reject(
                fn (string $model): bool => in_array(
                    $model,
                    tenantResourceMap(),
                    true,
                )
                || in_array($model, $partialModels, true)
                || array_key_exists($model, $optOut),
            )
            ->values()
            ->all();

        expect($untested)->toBe([]);
    });
});

describe('full resources', function () {
    test(
        'returns 404 when another organisation\'s record is requested',
        function (
            string $prefix,
            string $model,
        ) {
            $record = $this->organisationA->execute(
                fn () => $model::factory()->create(),
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
                            "{$prefix}.show",
                            $record->getKey(),
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
                            "{$prefix}.edit",
                            $record->getKey(),
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
                            "{$prefix}.update",
                            $record->getKey(),
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
                            "{$prefix}.destroy",
                            $record->getKey(),
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
                $model::withoutGlobalScopes()
                    ->whereKey($record->getKey())
                    ->exists(),
            )->toBeTrue();
        },
    )->with(
        fn () => collect(tenantResourceMap())
            ->map(
                fn (
                    string $model,
                    string $prefix,
                ): array => [$prefix, $model],
            )
            ->all(),
    );
});

describe('partial resources', function () {
    test(
        'returns 404 for the routes a partial resource actually has',
        function (
            string $prefix,
            string $model,
            array $actions,
        ) {
            $record = $this->organisationA->execute(
                fn () => $model::factory()->create(),
            );

            $userB = $this->memberOf($this->organisationB);

            $expected = [];
            $responses = [];

            foreach ($actions as $action) {
                $expected[$action] = 404;

                $request = $this->actingAsMemberOf(
                    $this->organisationB,
                    $userB,
                );

                $responses[$action] = match ($action) {
                    'show', 'edit' => $request
                        ->get(
                            route(
                                "{$prefix}.{$action}",
                                $record->getKey(),
                            ),
                        )
                        ->status(),

                    'update' => $request
                        ->put(
                            route(
                                "{$prefix}.update",
                                $record->getKey(),
                            ),
                            [],
                        )
                        ->status(),

                    'destroy' => $request
                        ->delete(
                            route(
                                "{$prefix}.destroy",
                                $record->getKey(),
                            ),
                        )
                        ->status(),
                };
            }

            expect($responses)->toBe($expected);

            expect(
                $model::withoutGlobalScopes()
                    ->whereKey($record->getKey())
                    ->exists(),
            )->toBeTrue();
        },
    )->with(
        fn () => collect(tenantPartialResourceMap())
            ->map(
                fn (
                    array $entry,
                    string $prefix,
                ): array => [
                    $prefix,
                    $entry['model'],
                    $entry['actions'],
                ],
            )
            ->all(),
    );
});

describe('bulk delete', function () {
    test(
        'does not bulk delete another organisation\'s records',
        function (
            string $prefix,
            string $model,
        ) {
            $record = $this->organisationA->execute(
                fn () => $model::factory()->create(),
            );

            $userB = $this->memberOf($this->organisationB);

            $this
                ->actingAsMemberOf(
                    $this->organisationB,
                    $userB,
                )
                ->post(
                    route("{$prefix}.bulk.delete"),
                    [
                        'ids' => [$record->getKey()],
                    ],
                );

            expect(
                $model::withoutGlobalScopes()
                    ->whereKey($record->getKey())
                    ->whereNull('deleted_at')
                    ->exists(),
            )->toBeTrue();
        },
    )->with(
        fn () => collect(tenantBulkResourceMap())
            ->map(
                fn (
                    string $model,
                    string $prefix,
                ): array => [$prefix, $model],
            )
            ->all(),
    );
});

describe('search', function () {
    test('does not leak another organisation\'s records through search', function () {
        $marker = 'Isolation-Probe-'.bin2hex(random_bytes(4));

        $this->organisationA->execute(
            fn () => Company::factory()->create([
                'name' => $marker,
            ]),
        );

        $this
            ->actingAsMemberOf($this->organisationB)
            ->getJson(
                route('search', [
                    'q' => $marker,
                ]),
            )
            ->assertOk()
            ->assertJsonMissing([
                'name' => $marker,
            ]);
    });
});

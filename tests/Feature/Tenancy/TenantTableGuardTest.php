<?php

use App\Traits\BelongsToOrganisation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

describe('table scoping', function () {
    test('has an organisation_id on every table that is not explicitly central', function () {
        $central = config('organisations.central_tables');

        $unscoped = collect(Schema::getTables())
            ->pluck('name')
            ->reject(fn (string $table): bool => in_array($table, $central, true))
            ->reject(fn (string $table): bool => Schema::hasColumn($table, 'organisation_id'))
            ->values()
            ->all();

        // A non-empty list names the tables to scope or to add to central_tables with a reason.
        expect($unscoped)->toBe([]);
    });

    test('has an organisation_id column for every scoped model', function () {
        $missing = collect(config('organisations.scoped_models'))
            ->reject(
                fn (string $model): bool => ! Schema::hasColumn(
                    (new $model())->getTable(),
                    'organisation_id'
                )
            )
            ->values()
            ->all();

        expect($missing)->toBe([]);
    });
});

describe('model scoping', function () {
    test('uses the BelongsToOrganisation trait on every scoped model', function () {
        $missing = collect(config('organisations.scoped_models'))
            ->reject(
                fn (string $model): bool => in_array(
                    BelongsToOrganisation::class,
                    class_uses_recursive($model),
                    true
                )
            )
            ->values()
            ->all();

        expect($missing)->toBe([]);
    });

    test('does not allow organisation_id to be mass assigned', function () {
        $fillable = collect(config('organisations.scoped_models'))
            ->filter(
                fn (string $model): bool => (new $model())->isFillable('organisation_id')
            )
            ->values()
            ->all();

        expect($fillable)->toBe([]);
    });
});

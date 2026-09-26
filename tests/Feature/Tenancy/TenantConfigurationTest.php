<?php

use App\Models\Organisation;
use App\Models\Setting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Multitenancy\Tasks\PrefixCacheTask;

uses(LazilyRefreshDatabase::class);

describe('multitenancy configuration', function () {
    test('uses the organisation model as the tenant', function () {
        expect(config('multitenancy.tenant_model'))->toBe(Organisation::class);
    });

    test('runs queued jobs in the tenant context by default', function () {
        expect(config('multitenancy.queues_are_tenant_aware_by_default'))->toBeTrue();
    });

    test('prefixes the cache per tenant', function () {
        expect(config('multitenancy.switch_tenant_tasks'))
            ->toContain(PrefixCacheTask::class);
    });
});

describe('organisation config lists', function () {
    test('keeps the central table allow-list free of duplicates', function () {
        $tables = config('organisations.central_tables');

        expect($tables)->toBe(array_values(array_unique($tables)));
    });

    test('keeps the scoped models list free of duplicates', function () {
        $models = config('organisations.scoped_models');

        expect($models)->toBe(array_values(array_unique($models)));
    });

    test('does not scope the Setting model', function () {
        expect(config('organisations.scoped_models'))->not->toContain(Setting::class);
        expect(config('organisations.central_tables'))->toContain('settings');
    });
});

<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(LazilyRefreshDatabase::class);

dataset('policies', function (): array {
    $policies = [];

    foreach (glob(__DIR__.'/../../../app/Policies/*Policy.php') ?: [] as $file) {
        $policy = 'App\\Policies\\'.basename($file, '.php');
        $model = 'App\\Models\\'.substr(basename($file, '.php'), 0, -6);

        if (class_exists($model)) {
            $policies[$policy] = [$model, $policy];
        }
    }

    return $policies;
});

test('every policy is registered for its model', function (
    string $model,
    string $policy
): void {
    expect(Gate::getPolicyFor($model))->toBeInstanceOf($policy);
})->with('policies');

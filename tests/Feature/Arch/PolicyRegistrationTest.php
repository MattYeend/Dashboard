<?php

use App\Contracts\Auditable;
use Illuminate\Support\Facades\Gate;

dataset('auditable_models', function (): array {
    $models = [];

    foreach (glob(__DIR__.'/../../app/Models/*.php') as $file) {
        $class = 'App\\Models\\'.basename($file, '.php');

        if (class_exists($class) && is_subclass_of($class, Auditable::class)) {
            $models[$class] = [$class];
        }
    }

    return $models;
});

it('has a registered policy for every auditable model', function (string $model): void {
    expect(Gate::getPolicyFor($model))->not->toBeNull();
})->with('auditable_models');

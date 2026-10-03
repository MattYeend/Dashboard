<?php

declare(strict_types=1);

use App\Contracts\Auditable;

dataset('auditable_models', function (): array {
    $models = [];

    foreach (glob(__DIR__.'/../../../app/Models/*.php') ?: [] as $file) {
        $class = 'App\\Models\\'.basename($file, '.php');

        if (
            class_exists($class)
            && is_subclass_of($class, Auditable::class)
            && ! (new ReflectionClass($class))->isAbstract()
        ) {
            $models[$class] = [$class];
        }
    }

    return $models;
});

describe('audit columns', function () {
    test('models do not allow audit columns to be mass assigned', function (
        string $class
    ): void {
        $instance = new $class;

        foreach (
            ['deleted_by', 'deleted_at', 'restored_by', 'restored_at'] as $column
        ) {
            expect($instance->isFillable($column))
                ->toBeFalse("{$class} allows {$column}");
        }
    })->with('auditable_models');
});

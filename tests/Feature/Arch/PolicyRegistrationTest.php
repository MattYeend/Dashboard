<?php

use App\Contracts\Auditable;
use App\Models\OrganisationMembership;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(LazilyRefreshDatabase::class);

dataset('auditable_models', function (): array {
    $models = [];

    /**
     * OrganisationMembership is a pivot-through model (organisation_user),
     * authorised via the parent Organisation policy rather than its own -
     * see Organisations\PolicyAuthorisationService::canInvite/canRemoveMember.
     */
    $ignored = [
        OrganisationMembership::class,
    ];

    foreach (glob(__DIR__.'/../../../app/Models/*.php') as $file) {
        $class = 'App\\Models\\'.basename($file, '.php');

        if (class_exists($class) && is_subclass_of($class, Auditable::class) && ! in_array($class, $ignored, true)) {
            $models[$class] = [$class];
        }
    }

    return $models;
});

it('has a registered policy for every auditable model', function (string $model): void {
    expect(Gate::getPolicyFor($model))->not->toBeNull();
})->with('auditable_models');

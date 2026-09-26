<?php

use App\Models\Organisation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\Concerns\ActsAsOrganisationMember;
use Tests\TestCase;

uses(ActsAsOrganisationMember::class);

beforeEach(function (): void {
    $this->setUpOrganisationIsolation();
});

afterEach(function (): void {
    Organisation::forgetCurrent();
});

describe('query isolation', function () {
    test('never returns another organisation\'s rows', function (): void {
        /** @var TestCase $this */

        $leaks = [];

        foreach (config('organisations.scoped_models') as $model) {
            $record = $this->organisationA->execute(
                fn () => $model::factory()->create()
            );

            $this->organisationB->execute(
                function () use ($model, $record, &$leaks): void {
                    $key = $record->getKey();

                    if ($model::query()->whereKey($key)->exists()) {
                        $leaks[] = "{$model}: query()->exists() leaked";
                    }

                    if ($model::query()->find($key) !== null) {
                        $leaks[] = "{$model}: find() leaked";
                    }

                    if ($model::query()->count() !== 0) {
                        $leaks[] = "{$model}: count() leaked";
                    }

                    if ($model::query()->pluck('id')->contains($key)) {
                        $leaks[] = "{$model}: pluck() leaked";
                    }

                    if (
                        in_array(
                            SoftDeletes::class,
                            class_uses_recursive($model),
                            true
                        )
                    ) {
                        if ($model::withTrashed()->whereKey($key)->exists()) {
                            $leaks[] = "{$model}: withTrashed() leaked";
                        }

                        if ($model::onlyTrashed()->whereKey($key)->exists()) {
                            $leaks[] = "{$model}: onlyTrashed() leaked";
                        }
                    }
                }
            );

            if (
                ! $model::withoutGlobalScopes()
                    ->whereKey($record->getKey())
                    ->exists()
            ) {
                $leaks[] =
                    "{$model}: factory did not persist a record " .
                    '(make the factory self-sufficient)';
            }
        }

        expect($leaks)->toBe([]);
    });

    test('cannot update or delete another organisation\'s rows', function (): void {
        /** @var TestCase $this */

        $failures = [];

        foreach (config('organisations.scoped_models') as $model) {
            $record = $this->organisationA->execute(
                fn () => $model::factory()->create()
            );

            $affected = $this->organisationB->execute(
                function () use ($model, $record): int {
                    $updated = $model::query()
                        ->whereKey($record->getKey())
                        ->update([
                            'updated_at' => now()->addYear(),
                        ]);

                    $deleted = $model::query()
                        ->whereKey($record->getKey())
                        ->delete();

                    return $updated + $deleted;
                }
            );

            if ($affected !== 0) {
                $failures[] =
                    "{$model}: mass update or delete reached another " .
                    "organisation ({$affected} rows)";
            }
        }

        expect($failures)->toBe([]);
    });
});

describe('fail closed behaviour', function () {
    test('fails closed when there is no current organisation', function (): void {
        /** @var TestCase $this */

        $visible = [];

        foreach (config('organisations.scoped_models') as $model) {
            $this->organisationA->execute(
                fn () => $model::factory()->create()
            );
        }

        Organisation::forgetCurrent();

        foreach (config('organisations.scoped_models') as $model) {
            if ($model::query()->count() !== 0) {
                $visible[] = $model;
            }
        }

        expect($visible)->toBe([]);
    });

    test('refuses to create a record for a different organisation', function (): void {
        /** @var TestCase $this */

        $failures = [];

        foreach (config('organisations.scoped_models') as $model) {
            try {
                $this->organisationB->execute(
                    fn () => $model::factory()->create([
                        'organisation_id' => $this->organisationA->getKey(),
                    ])
                );

                $failures[] =
                    "{$model}: accepted a foreign organisation_id";
            } catch (Throwable) {
                // Refused, as required.
            }
        }

        expect($failures)->toBe([]);
    });
});

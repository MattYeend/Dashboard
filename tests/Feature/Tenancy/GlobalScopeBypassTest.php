<?php

use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

describe('global scope bypass allow-list', function () {
    test('does not bypass global scopes outside the reviewed allow-list', function () {
        $allowed = config('organisations.global_scope_bypass_allow_list', []);

        $offenders = collect(
            Finder::create()->files()->in(app_path())->name('*.php')
        )
            ->filter(
                fn ($file): bool => preg_match(
                    '/withoutGlobalScope(s)?\s*\(/',
                    $file->getContents()
                ) === 1
            )
            ->map(
                fn ($file): string => str_replace(
                    '\\',
                    '/',
                    Str::after($file->getPathname(), base_path().DIRECTORY_SEPARATOR)
                )
            )
            ->reject(fn (string $path): bool => in_array($path, $allowed, true))
            ->values()
            ->all();

        expect($offenders)->toBe([]);
    });
});
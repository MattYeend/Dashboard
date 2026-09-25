<?php

use App\Models\Log;
use App\Services\Logs\IntegrityService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(LazilyRefreshDatabase::class);

/**
 * Resolve the integrity service under test.
 */
function integrityService(): IntegrityService
{
    return app(IntegrityService::class);
}

beforeEach(function () {
    config(['audit.hmac_key' => str_repeat('k', 40)]);

    foreach ([1, 2, 3] as $number) {
        Log::query()->create([
            'action_id' => Log::ACTION_NONE,
            'data' => ['before' => ['n' => $number], 'after' => ['n' => $number + 1]],
        ]);
    }
});

describe('seal', function () {
    test('seals rows into a chain that verifies', function () {
        expect(integrityService()->seal())->toBe(3)
            ->and(integrityService()->verify())->toBe([]);
    });

    test('refuses to run without a strong key', function () {
        config(['audit.hmac_key' => 'short']);

        expect(fn () => integrityService()->seal())->toThrow(RuntimeException::class);
    });
});

describe('verify', function () {
    test('detects a modified row', function () {
        integrityService()->seal();

        DB::table('logs')->where('sequence', 2)->update(['data' => json_encode(['after' => ['n' => 999]])]);

        $failures = integrityService()->verify();

        expect($failures)->not->toBe([])
            ->and(collect($failures)->pluck('sequence'))->toContain(2);
    });

    test('detects a removed row', function () {
        integrityService()->seal();

        DB::table('logs')->where('sequence', 2)->delete();

        expect(collect(integrityService()->verify())->pluck('reason')->implode(' '))->toContain('gap');
    });

    test('still verifies after the oldest rows are pruned', function () {
        integrityService()->seal();

        DB::table('logs')->where('sequence', 1)->delete();

        expect(integrityService()->verify())->toBe([]);
    });
});

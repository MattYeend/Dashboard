<?php

use App\Models\Log;
use App\Services\SensitiveDataMaskerService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

describe('routes', function () {
    test('has no mutating routes for audit data', function () {
        $mutating = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'audit-trail.'))
            ->filter(fn ($route) => array_diff($route->methods(), ['GET', 'HEAD']) !== [])
            ->map(fn ($route) => $route->getName())
            ->values()
            ->all();

        expect($mutating)->toBe([]);
    });
});

describe('index', function () {
    test('authenticated user with permission can view the audit trail', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/audit-trail')
            ->assertOk();
    });

    test('unauthenticated user cannot view the audit trail', function () {
        $this->get('/audit-trail')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view the audit trail', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/audit-trail')
            ->assertStatus(403);
    });
});

describe('immutability', function () {
    test('refuses to update or delete a log entry through Eloquent', function () {
        $log = Log::query()->create(['action_id' => Log::ACTION_NONE, 'data' => []]);

        expect(fn () => $log->update(['action_id' => 1]))->toThrow(LogicException::class)
            ->and(fn () => $log->delete())->toThrow(LogicException::class);
    });
});

describe('masking', function () {
    test('masks sensitive keys at any depth', function () {
        $masked = app(SensitiveDataMaskerService::class)->mask([
            'name' => 'Alex',
            'password' => 'hunter2',
            'nested' => ['api_key' => 'abc', 'two_factor_secret' => 'xyz', 'city' => 'Leicester'],
        ]);

        expect($masked['name'])->toBe('Alex')
            ->and($masked['password'])->toBe(SensitiveDataMaskerService::MASK)
            ->and($masked['nested']['api_key'])->toBe(SensitiveDataMaskerService::MASK)
            ->and($masked['nested']['two_factor_secret'])->toBe(SensitiveDataMaskerService::MASK)
            ->and($masked['nested']['city'])->toBe('Leicester');
    });
});

describe('export', function () {
    test('user with permission can export the audit trail', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/audit-trail/export?'.http_build_query([
                'date_from' => now()->subDay()->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    });

    test('user without permission cannot export the audit trail', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/audit-trail/export?'.http_build_query([
                'date_from' => now()->subDay()->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertStatus(403);
    });
});
